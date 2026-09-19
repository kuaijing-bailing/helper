<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */

namespace Bailing\Helper;

use Bailing\Constants\Code\Common\CommonCode;
use Bailing\Exception\BusinessException;
use Bailing\JsonRpc\Org\OrgUserServiceInterface;
use Bailing\Model\BailingOrgConfig;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CacheEvict;
use Hyperf\Cache\Annotation\CachePut;
use Hyperf\Codec\Json;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;

class OrgConfigHelper
{
    /**
     * 创建表（由于支持了migration，弃用）.
     */
    #[Cacheable(prefix: 'bailingOrgConfigTable', ttl: 86400)]
    public static function createTable(): string
    {
        return 'bailingOrgConfigTable';
    }

    /**
     * 初始化机构配置表，并补齐备注、带注释原文及系统后台来源字段（由于支持了migration，弃用）.
     */
    public static function createTableCode(): bool
    {
        return true;
    }

    /**
     * 查询系统后台保存的配置名称，只返回指定机构的名称并跨 index 去重.
     * @param int $orgId 机构 ID，0 表示全局配置
     * @return array<string> 按名称排序的去重列表，不返回配置值
     * @throws BusinessException 当前服务尚未迁移来源字段
     */
    public static function getSystemConfigNames(int $orgId): array
    {
        return BailingOrgConfig::query()
            ->where('org_id', $orgId)
            ->where('is_system', true)
            ->where('name', '<>', '')
            ->distinct()
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    /**
     * 查询指定机构和配置名称的后台配置 index，NULL 归并为空字符串，保留字符串零.
     * @param int $orgId 机构 ID，0 表示全局配置
     * @param string $name 已选择的配置名称
     * @return array<string> 去重排序的 index，空字符串表示无 index
     */
    public static function getSystemConfigIndexes(int $orgId, string $name): array
    {
        $indexes = BailingOrgConfig::query()
            ->where('org_id', $orgId)
            ->where('name', $name)
            ->where('is_system', true)
            ->distinct()
            ->pluck('index')
            ->all();

        $indexes = array_values(array_unique(array_map(static fn ($index) => (string) $index, $indexes)));
        sort($indexes, SORT_STRING);
        return $indexes;
    }

    /**
     * 为配置编辑器精确读取 index 对应的值及注释；空 index 只匹配空字符串或 NULL.
     * @return array{result: string, value_document: ?string, supports_comments: bool}
     */
    public static function getConfigForEditor(int $orgId, string $name, int|string $index = ''): array
    {
        $where = ['org_id' => $orgId, 'name' => $name];
        $index !== '' && $where['index'] = $index;
        // 同一次查询取得值和原文，避免组合缓存旧值与最新注释.
        $query = BailingOrgConfig::query()->where($where);
        if ($index === '') {
            $query->where(static fn ($query) => $query->where('index', '')->orWhereNull('index'));
        }
        $config = $query->first();
        $value = (string) ($config?->value ?? '');
        $document = $config?->value_document;
        if (! is_string($document) || ! OrgConfigDocumentHelper::matchesValue($document, $value)) {
            $document = null;
        }
        return [
            'result' => $value,
            'value_document' => $document,
            'supports_comments' => Schema::hasColumn('bailing_org_config', 'value_document'),
        ];
    }

    /**
     * 读取数组格式的配置值（缓存10分钟），自行保证写时的缓存是数组.
     */
    public static function getConfigArr(int $orgId, string $name, int|string $index = '', bool $getOrgServiceData = false): array
    {
        $config = self::getConfig($orgId, $name, $index, $getOrgServiceData);
        if (empty($config)) {
            return [];
        }
        return Json::decode($config);
    }

    /**
     * 读取配置值（缓存10分钟）.
     * @param int $orgId 机构ID
     * @param string $name 配置名
     * @param int|string $index 唯一索引值，用于细项配置（例如 项目ID_楼宇ID，店铺ID）
     * @param bool $getOrgServiceData 从org微服务获取
     */
    #[Cacheable(prefix: 'bailingOrgConfig', value: '_#{orgId}_#{name}_#{index}_#{getOrgServiceData}', ttl: 600)]
    public static function getConfig(int $orgId, string $name, int|string $index = '', bool $getOrgServiceData = false): string
    {
        if ($getOrgServiceData) {
            $orgResult = container()->get(OrgUserServiceInterface::class)->call('getBailingOrgConfig', ['org_id' => $orgId, 'name' => $name, 'index' => $index]);
            if (ApiHelper::checkDataOk($orgResult)) {
                return (string) $orgResult['data']['result'];
            }
            return '';
        }
        $where = [
            'org_id' => $orgId,
            'name' => $name,
        ];
        $index !== '' && $where['index'] = $index;
        $configValue = Db::table('bailing_org_config')->where($where)->value('value');
        return (string) $configValue;
    }

    /**
     * 写数组格式的配置值.
     */
    public static function setConfigArr(int $orgId, string $name, array $value, int|string $index = '', bool $setOrgServiceData = false): string
    {
        return self::setConfig($orgId, $name, Json::encode($value ?: []), $index, $setOrgServiceData);
    }

    /**
     * 写缓存.因为每次应该先读出来渲染页面，然后保存再写。所以不创建表。
     * 系统后台保存时空 index 精确匹配无索引配置，普通业务调用保留原有匹配规则.
     * @param int $orgId 机构ID
     * @param string $name 配置名称
     * @param string $value 配置值
     * @param int|string $index 唯一索引值，用于细项配置（例如 项目ID_楼宇ID，店铺ID）
     * @param null|string $valueDocument 本地编辑器注释原文；null 表示保留仍匹配的原文，空字符串表示清除
     * @param bool $fromSystem 是否从本服务的系统后台保存；仅置为 true，普通业务写入保留已有来源
     * @throws BusinessException 系统后台保存时来源字段尚未迁移，或尝试经 RPC 写入来源
     * @throws \JsonException 注释原文不是合法 JSONC
     */
    #[CachePut(prefix: 'bailingOrgConfig', value: '_#{orgId}_#{name}_#{index}_#{setOrgServiceData}', ttl: 600)]
    public static function setConfig(int $orgId, string $name, string $value, int|string $index = '', bool $setOrgServiceData = false, ?string $valueDocument = null, bool $fromSystem = false): string
    {
        if ($valueDocument !== null) {
            if ($setOrgServiceData || ($valueDocument !== '' && ! Schema::hasColumn('bailing_org_config', 'value_document'))) {
                throw new BusinessException(ApiHelper::NORMAL_ERROR, CommonCode::CONFIG_COMMENTS_UNAVAILABLE->genI18nMsg([], true));
            }
            if ($valueDocument !== '') {
                OrgConfigDocumentHelper::toJson($valueDocument);
                if (! OrgConfigDocumentHelper::matchesValue($valueDocument, $value)) {
                    throw new BusinessException(ApiHelper::NORMAL_ERROR, CommonCode::CONFIG_DOCUMENT_MISMATCH->genI18nMsg([], true));
                }
            }
        }
        if ($setOrgServiceData) {
            $orgResult = container()->get(OrgUserServiceInterface::class)->call('setBailingOrgConfig', ['org_id' => $orgId, 'name' => $name, 'value' => $value, 'index' => $index]);
            if (ApiHelper::checkDataOk($orgResult)) {
                return (string) $orgResult['data']['result'];
            }
            return '';
        }

        $where = [
            'org_id' => $orgId,
            'name' => $name,
        ];
        $index !== '' && $where['index'] = $index;
        $query = BailingOrgConfig::query()->where($where);
        if ($fromSystem && $index === '') {
            $query->where(static fn ($query) => $query->where('index', '')->orWhereNull('index'));
        }
        $config = $query->first() ?? new BailingOrgConfig();
        if (! $config->exists) {
            $config->org_id = $orgId;
            $config->name = $name;
            $config->index = $index;
        }
        if ($valueDocument === '' && ! Schema::hasColumn('bailing_org_config', 'value_document')) {
            $valueDocument = null;
        }
        if ($valueDocument !== null) {
            $config->value_document = $valueDocument === '' ? null : $valueDocument;
        } elseif ($config->value_document && ! OrgConfigDocumentHelper::matchesValue($config->value_document, $value)) {
            // 业务程序更新配置后，不再展示与新值不一致的旧原文.
            $config->value_document = null;
        }
        $config->value = $value;
        if ($fromSystem) {
            $config->is_system = true;
        }
        $config->save();
        return $value;
    }

    /**
     * 删除配置值并清除缓存.
     * @param int $orgId 机构ID
     * @param string $name 配置名
     * @param int|string $index 唯一索引值，用于细项配置（例如 项目ID_楼宇ID，店铺ID）
     */
    #[CacheEvict(prefix: 'bailingOrgConfig', value: '_#{orgId}_#{name}_#{index}_#{setOrgServiceData}')]
    public static function deleteConfig(int $orgId, string $name, int|string $index = '', bool $setOrgServiceData = false): int
    {
        $where = [
            'org_id' => $orgId,
            'name' => $name,
        ];
        $index !== '' && $where['index'] = $index;
        $configs = BailingOrgConfig::query()->where($where)->get();
        $deletedCount = 0;
        foreach ($configs as $config) {
            $deletedCount += (int) $config->delete();
        }
        return $deletedCount;
    }

    /**
     * 清除配置值（缓存10分钟）.
     * @param int $orgId 机构ID
     * @param string $name 配置名
     * @param int|string $index 唯一索引值，用于细项配置（例如 项目ID_楼宇ID，店铺ID）
     */
    #[CacheEvict(prefix: 'bailingOrgConfig', value: '_#{orgId}_#{name}_#{index}_#{setOrgServiceData}')]
    public static function clearCache(int $orgId, string $name, int|string $index = '', bool $setOrgServiceData = false): void {}
}

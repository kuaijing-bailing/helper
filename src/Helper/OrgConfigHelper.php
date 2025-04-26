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

use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Annotation\CacheEvict;
use Hyperf\Cache\Annotation\CachePut;
use Hyperf\Codec\Json;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;

class OrgConfigHelper
{
    /**
     * 创建表.
     */
    #[Cacheable(prefix: 'bailingOrgConfigTable', ttl: 86400)]
    public static function createTable(): string
    {
        self::createTableCode();
        return 'bailingOrgConfigTable';
    }

    public static function createTableCode(): bool
    {
        if (! Schema::hasTable('bailing_org_config')) {
            Schema::create('bailing_org_config', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('org_id')->nullable()->comment('机构ID')->index('idx_org_id');
                $table->string('index', 100)->nullable()->comment('额外的唯一索引名，例如（项目ID_楼宇ID）')->index('idx_index');
                $table->string('name', 100)->nullable()->comment('名称')->index('idx_name');
                $table->text('value')->nullable()->comment('值');
                $table->string('remark', 100)->nullable()->comment('备注');
                $table->timestamps();
                $table->comment('机构配置表');
            });
        } elseif (! Schema::hasColumn('bailing_org_config', 'remark')) {
            Schema::table('bailing_org_config', function (Blueprint $table) {
                $table->string('remark', 100)->nullable()->comment('备注')->after('value');
            });
        }
        return true;
    }

    /**
     * 读取数组格式的配置值（缓存10分钟），自行保证写时的缓存是数组.
     */
    public static function getConfigArr(int $orgId, string $name, string $index = ''): array
    {
        $config = self::getConfig($orgId, $name, $index);
        if (empty($config)) {
            return [];
        }
        return Json::decode($config);
    }

    /**
     * 读取配置值（缓存10分钟）.
     * @param int $orgId 机构ID
     * @param string $name 配置名
     * @param string $index 唯一索引值，用于细项配置（例如 项目ID_楼宇ID，店铺ID）
     * @return string
     */
    #[Cacheable(prefix: 'bailingOrgConfig', value: '_#{orgId}_#{name}_#{index}', ttl: 600)]
    public static function getConfig(int $orgId, string $name, string $index = ''): string
    {
        //自动创建表，通过缓存来判断有没有创建过.
        if (! env('BAILING_ORG_CONFIG_BUILDED')) {
            self::createTable();
        }

        $where = [
            'org_id' => $orgId,
            'name' => $name,
        ];
        ! empty($index) && $where['index'] = $index;
        $configValue = Db::table('bailing_org_config')->where($where)->value('value');
        return (string) $configValue;
    }

    /**
     * 写数组格式的配置值.
     */
    public static function setConfigArr(int $orgId, string $name, array $value, string $index = ''): string
    {
        return self::setConfig($orgId, $name, Json::encode($value ?: []), $index);
    }

    /**
     * 写缓存.因为每次应该先读出来渲染页面，然后保存再写。所以不创建表。
     * @param int $orgId 机构ID
     * @param string $name 配置名称
     * @param string $value 配置值
     * @param string $index 唯一索引值，用于细项配置（例如 项目ID_楼宇ID，店铺ID）
     */
    #[CachePut(prefix: 'bailingOrgConfig', value: '_#{orgId}_#{name}_#{index}', ttl: 600)]
    public static function setConfig(int $orgId, string $name, string $value, string $index = ''): string
    {
        $where = [
            'org_id' => $orgId,
            'name' => $name,
        ];
        ! empty($index) && $where['index'] = $index;
        $configArr = Db::table('bailing_org_config')->where($where)->first();
        if (! $configArr) {
            Db::table('bailing_org_config')->insert([
                'org_id' => $orgId,
                'name' => $name,
                'value' => $value,
                'index' => $index,
                'created_at' => getTime(),
                'updated_at' => getTime(),
            ]);
        } else {
            Db::table('bailing_org_config')->where($where)->update([
                'value' => $value,
                'updated_at' => getTime(),
            ]);
        }
        return $value;
    }

    /**
     * 清除配置值（缓存10分钟）.
     * @param int $orgId 机构ID
     * @param string $name 配置名
     * @param string $index 唯一索引值，用于细项配置（例如 项目ID_楼宇ID，店铺ID）
     */
    #[CacheEvict(prefix: 'bailingOrgConfig', value: '_#{orgId}_#{name}_#{index}')]
    public static function clearCache(int $orgId, string $name, string $index = ''): void
    {
    }
}

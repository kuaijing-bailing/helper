<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */

namespace Bailing\Office;

use Bailing\Office\Excel\XlsWriter;
use Hyperf\Codec\Json;
use Hyperf\DbConnection\Model\Model;
use Psr\Http\Message\ResponseInterface;

class Collection extends \Hyperf\Collection\Collection
{
    /**
     * 使用 XlsWriter 按 DTO 定义导出 XLSX，运行环境须安装 xlswriter 扩展.
     * @param string $dto 导出字段定义的 DTO 类名
     * @param string $filename 不含扩展名的导出文件名
     * @param null|array|\Closure $closure 导出数据或数据回调，为空时使用当前集合
     * @param array $extra 额外字段定义
     * @param bool $isDemo 是否导出示例模板
     * @param int $orgId 机构 ID
     * @param array $infos 导出上下文和选项
     */
    public function export(string $dto, string $filename, array|\Closure|null $closure = null, array $extra = [], bool $isDemo = false, int $orgId = 0, array $infos = []): ResponseInterface|string
    {
        $data = is_null($closure) ? $this->toArray() : $closure;
        $excel = new XlsWriter($dto, $extra, $isDemo, $orgId, $infos);
        try {
            return $excel->export($filename, $data, null, $isDemo, $orgId, $infos);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 使用 XlsWriter 按 DTO 定义导入 XLSX，运行环境须安装 xlswriter 扩展.
     * @param string $dto 导入字段定义的 DTO 类名
     * @param Model $model 导入数据使用的模型
     * @param null|\Closure $closure 自定义导入数据处理回调
     * @param array $extra 额外字段定义
     * @param int $orgId 机构 ID
     * @param array $infos 导入上下文和选项
     */
    public function import(string $dto, Model $model, ?\Closure $closure = null, array $extra = [], int $orgId = 0, array $infos = []): bool
    {
        $excel = new XlsWriter($dto, $extra, false, $orgId);
        try {
            return $excel->import($model, $closure, $orgId, $infos);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 写错误的缓存.
     * @param string $keyPrefix 缓存前缀
     * @param string $dto Dto类
     * @param string $filename 错误文件名
     * @param array $data 错误数据
     * @param int $orgId 机构ID
     * @param int $ttl 缓存时间，默认5分钟
     * @return bool
     */
    public function setImportErrorCache(string $keyPrefix, string $dto, string $filename, array $data, int $orgId = 0, int $ttl = 300): string
    {
        $errorRedisKey = $keyPrefix . ':' . $orgId . ':' . uniqid();
        $cacheData = [
            'org_id' => $orgId,
            'dto' => $dto,
            'data' => $data,
            'filename' => $filename,
        ];
        redis()->set($errorRedisKey, Json::encode($cacheData), $ttl);
        return $errorRedisKey;
    }
}

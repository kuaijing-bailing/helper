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

use Bailing\Office\Excel\PhpOffice;
use Bailing\Office\Excel\XlsWriter;
use Hyperf\Codec\Json;
use Hyperf\DbConnection\Model\Model;
use Psr\Http\Message\ResponseInterface;

class Collection extends \Hyperf\Collection\Collection
{
    public function export(string $dto, string $filename, array|\Closure $closure = null, array $extra = [], bool $isDemo = false, int $orgId = 0): ResponseInterface
    {
        $excelDrive = \Hyperf\Config\config('excel.drive', 'auto');
        if ($excelDrive === 'auto') {
            $excel = extension_loaded('xlswriter') ? new XlsWriter($dto, $extra, $isDemo, $orgId) : new PhpOffice($dto);
        } else {
            $excel = $excelDrive === 'xlsWriter' ? new XlsWriter($dto, $extra, $isDemo, $orgId) : new PhpOffice($dto);
        }

        return $excel->export($filename, is_null($closure) ? $this->toArray() : $closure, null, $isDemo, $orgId);
    }

    public function import(string $dto, Model $model, ?\Closure $closure = null, array $extra = [], int $orgId = 0): bool
    {
        $excelDrive = \Hyperf\Config\config('excel.drive', 'auto');
        if ($excelDrive === 'auto') {
            $excel = extension_loaded('xlswriter') ? new XlsWriter($dto, $extra, false, $orgId) : new PhpOffice($dto);
        } else {
            $excel = $excelDrive === 'xlsWriter' ? new XlsWriter($dto, $extra, false, $orgId) : new PhpOffice($dto);
        }
        return $excel->import($model, $closure, $orgId);
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
        $cacheData  = [
            'org_id' => $orgId,
            'dto' => $dto,
            'data' => $data,
            'filename' => $filename,
        ];
        redis()->set($errorRedisKey, Json::encode($cacheData), $ttl);
        return $errorRedisKey;
    }
}

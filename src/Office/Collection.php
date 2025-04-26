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
use Hyperf\DbConnection\Model\Model;

class Collection extends \Hyperf\Collection\Collection
{
    public function export(string $dto, string $filename, array|\Closure $closure = null): \Psr\Http\Message\ResponseInterface
    {
        $excelDrive = \Hyperf\Config\config('excel.drive', 'auto');
        if ($excelDrive === 'auto') {
            $excel = extension_loaded('xlswriter') ? new XlsWriter($dto) : new PhpOffice($dto);
        } else {
            $excel = $excelDrive === 'xlsWriter' ? new XlsWriter($dto) : new PhpOffice($dto);
        }

        return $excel->export($filename, is_null($closure) ? $this->toArray() : $closure);
    }

    public function import(string $dto, Model $model, ?\Closure $closure = null): bool
    {
        $excelDrive = \Hyperf\Config\config('excel.drive', 'auto');
        if ($excelDrive === 'auto') {
            $excel = extension_loaded('xlswriter') ? new XlsWriter($dto) : new PhpOffice($dto);
        } else {
            $excel = $excelDrive === 'xlsWriter' ? new XlsWriter($dto) : new PhpOffice($dto);
        }
        return $excel->import($model, $closure);
    }
}

<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\Office\Interfaces;

use Hyperf\DbConnection\Model\Model;

interface ExcelPropertyInterface
{
    public function import(Model $model, ?\Closure $closure = null): bool;

    public function export(string $filename, array|\Closure $closure): \Psr\Http\Message\ResponseInterface|string;
}

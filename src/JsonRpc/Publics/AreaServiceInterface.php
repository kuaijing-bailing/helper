<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\JsonRpc\Publics;

interface AreaServiceInterface
{
    /**
     * 获取单个数据.
     */
    public function getArea(int $areaCode): array;

    public function getAreaByShortName(string $name): array;

    public function getAreaByName(string $name): array;

    /**
     * 根据中心坐标获取周边的区域地点.
     */
    public function getAreaListByLocationDistance(float $lng, float $lat, float $distance, int $level): array;
}

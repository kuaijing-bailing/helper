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

use Bailing\Helper\ApiHelper;
use Hyperf\RpcClient\AbstractServiceClient;

class AreaServiceConsumer extends AbstractServiceClient implements AreaServiceInterface
{
    /**
     * 定义对应服务提供者的服务名称.
     */
    protected string $serviceName = 'AreaService';

    /**
     * 定义对应服务提供者的服务协议.
     */
    protected string $protocol = 'jsonrpc-http';

    /**
     * 获取单个数据.
     */
    public function getArea(int $areaCode): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('areaCode'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    public function getAreaByShortName(string $name): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('name'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    public function getAreaByName(string $name): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('name'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 根据中心坐标获取周边的区域地点.
     */
    public function getAreaListByLocationDistance(float $lng, float $lat, float $distance, int $level): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('lng', 'lat', 'distance', 'level'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }
}

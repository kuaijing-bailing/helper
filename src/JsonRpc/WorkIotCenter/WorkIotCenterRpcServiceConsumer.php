<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\JsonRpc\WorkIotCenter;

use Bailing\Helper\ApiHelper;
use Hyperf\RpcClient\AbstractServiceClient;

class WorkIotCenterRpcServiceConsumer extends AbstractServiceClient implements WorkIotCenterRpcServiceInterface
{
    /**
     * 定义对应服务提供者的服务名称.
     */
    protected string $serviceName = 'WorkIotCenterRpcService';

    /**
     * 定义对应服务提供者的服务协议.
     */
    protected string $protocol = 'jsonrpc-http';

    public function call(string $method, array $param): array
    {
        try {
            if (! $method) {
                throw new \Exception('method不存在，请传参');
            }
            return $this->__request(__FUNCTION__, compact('method', 'param'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }
}

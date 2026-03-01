<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\JsonRpc\WorkHr;

use Bailing\Helper\ApiHelper;
use Hyperf\RpcClient\AbstractServiceClient;

class MailUserServiceConsumer extends AbstractServiceClient implements MailUserServiceInterface
{
    /**
     * 定义对应服务提供者的服务名称.
     */
    protected string $serviceName = 'MailUserService';

    /**
     * 定义对应服务提供者的服务协议.
     */
    protected string $protocol = 'jsonrpc-http';

    /**
     * 同步邮箱用户.
     */
    public function syncMailUser(array $data): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('data'));
        } catch (\Exception $exception) {
            stdLog()->warning('MailUserServiceConsumer Error', ['method' => __FUNCTION__, 'param' => $data]);
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }
}

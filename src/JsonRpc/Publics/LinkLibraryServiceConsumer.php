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

class LinkLibraryServiceConsumer extends AbstractServiceClient implements LinkLibraryServiceInterface
{
    /**
     * 定义对应服务提供者的服务名称.
     */
    protected string $serviceName = 'LinkLibraryService';

    /**
     * 定义对应服务提供者的服务协议.
     */
    protected string $protocol = 'jsonrpc-http';

    /**
     * 添加链接.
     */
    public function addLink(array $linkData): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('linkData'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }
}

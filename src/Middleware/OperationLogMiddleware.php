<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\Middleware;

use Bailing\Amqp\Producer\OperationLogProducer;
use Bailing\Helper\JwtHelper;
use Bailing\Helper\RequestHelper;
use Bailing\Helper\StrHelper;
use Hyperf\Amqp\Producer;
use Hyperf\Amqp\Result;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class OperationLogMiddleware implements MiddlewareInterface
{
    public const SYSTEM_JWT_TOKEN = 'system-token';

    public const ORG_JWT_TOKEN = 'org-token';

    public const USER_JWT_TOKEN = 'user-token';

    public const GUEST_JWT_TOKEN = 'guest-token';

    public const DATAV_JWT_TOKEN = 'datav-token';

    protected ContainerInterface $container;

    protected RequestInterface $request;

    protected HttpResponse $response;

    public function __construct(ContainerInterface $container, HttpResponse $response, RequestInterface $request)
    {
        $this->container = $container;
        $this->response = $response;
        $this->request = $request;
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $result = $handler->handle($request);

        $isDownload = false;
        if (! empty($result->getHeader('content-description')) && ! empty($result->getHeader('content-transfer-encoding'))) {
            $isDownload = true;
        }

        $ip = RequestHelper::getClientIp();
        $operationLog = [
            'org_id' => 0,
            'time' => date('Y-m-d H:i:s', $request->getServerParams()['request_time']),
            'method' => $request->getServerParams()['request_method'],
            'router' => $request->getServerParams()['path_info'],
            'protocol' => $request->getServerParams()['server_protocol'],
            'ip' => $ip,
            'service_name' => config('app_name'),
            'request_data' => $this->request->all(),
            'response_code' => $result->getStatusCode(),
            'response_data' => $isDownload ? '文件下载' : $result->getBody()->getContents(),
        ];

        $keyLabel = '';
        if ($this->request->header(self::SYSTEM_JWT_TOKEN)) {
            $keyLabel = 'SYSTEM';
        } elseif ($this->request->header(self::ORG_JWT_TOKEN)) {
            $keyLabel = 'ORG';
        } elseif ($this->request->header(self::USER_JWT_TOKEN)) {
            $keyLabel = 'USER';
            $operationLog['org_id'] = $this->request->input('org_id', 0);
        } elseif ($this->request->header(self::DATAV_JWT_TOKEN)) {
            $keyLabel = 'DATAV';
        }

        //获取用户登录信息.
        $user_data = $this->getUserData($keyLabel);
        if ($user_data) {
            $operationLog['user_data'] = $user_data;
            if ($keyLabel == 'ORG') {
                $operationLog['org_id'] = $user_data['org_id'] ?: 0;
            }
        } else {
            return $result;
        }

        // GET请求的不存
        if ($operationLog['method'] == 'GET') {
            return $result;
        }
        // 新的固定列表查询页，不存
        if ($operationLog['method'] == 'POST' && str_ends_with($operationLog['router'], '/query')) {
            return $result;
        }

        // 将日志存储到amqp中
        $message = new OperationLogProducer($operationLog);
        $producer = container()->get(Producer::class);
        $proResult = $producer->produce($message);
        stdLog()->debug('OperationLogProducer amqp', [$proResult]);

        return $result;
    }

    /**
     * 获取用户登录信息.
     * @param mixed $keyLabel
     */
    public function getUserData($keyLabel): array
    {
        //系统后台用户信息  {"id":1,"name":"admin","phone_country":null,"phone":"12345678910","last_time":"2023-01-11 10:46:12","last_ip":"127.0.0.1","role_id":null,"level":99,"account":"admin"}
        //机构后台用户信息  {"id":1,"phone_country":86,"phone":"12345678910","last_time":"2023-01-13 17:44:48","last_ip":"127.0.0.1","user_id":1,"name":"张三","org_id":1,"org_name":"啊屋","role_id":0,"level":99,"isSuper":true,"isLayerAdmin":false}
        //移动端用户信息   {"id":1,"phone_country":86,"phone":"12345678910","last_time":"2023-01-31 11:39:58","last_ip":"127.0.0.1","sharer":[]}
        $userData = [];
        if (! empty($keyLabel)) {
            $jwtData = JwtHelper::userData($keyLabel);
            //stdLog()->info('用户登录信息', [$keyLabel, $jwtData]);
            if ($jwtData) {
                $userData = [
                    'id' => $jwtData->id,
                    'name' => $jwtData->name ?? '',
                    'phone' => $jwtData->phone ?? '',
                    'org_id' => $jwtData->org_id ?? 0,
                    'origin' => strtolower($keyLabel),
                ];
            }
        }

        return $userData;
    }
}

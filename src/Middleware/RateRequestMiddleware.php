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

use Bailing\Helper\ApiHelper;
use Bailing\Helper\AesHelper;
use Bailing\Helper\RequestHelper;
use Hyperf\Codec\Json;
use Hyperf\Context\Context;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * 弃用，使用 RateRequest 注解.
 */
class RateRequestMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;

    protected RequestInterface $request;

    protected HttpResponse $response;

    public function __construct(ContainerInterface $container, HttpResponse $response, RequestInterface $request)
    {
        $this->container = $container;
        $this->response = $response;
        $this->request = $request;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $classMethod = explode(':', RequestHelper::getAdminModule());
        $handleArr = request()->all();

        //如果为空，认定为raw请求
        if (! $handleArr) {
            $handleArr = [
                'body' => request()->getBody()->getContents(),
            ];
        }

        $handleArr['class'] = $classMethod[0];
        $handleArr['method'] = $classMethod[1];

        $redis = redis();

        $strKey = 'rate_request:' . AesHelper::digest(serialize($handleArr));

        $result = $redis->set($strKey, Json::encode($handleArr), ['NX', 'EX' => 6]);

        if (empty($result)) {
            stdLog()->warning('RateRequestMiddleware', [$handleArr]);
            return self::json('请求正在执行，请稍后再试');
        }

        $result = $handler->handle($request);

        //删redis缓存
        $redis->del($strKey);

        return $result;
    }

    private static function json(string $msg, int $errCode = ApiHelper::NORMAL_ERROR)
    {
        $body = new SwooleStream(Json::encode(ApiHelper::genErrorData($msg, $errCode)));
        return Context::get(ResponseInterface::class)
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withBody($body);
    }
}

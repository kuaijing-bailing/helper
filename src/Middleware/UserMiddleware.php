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
use Bailing\Helper\JwtHelper;
use Hyperf\Context\Context;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Codec\Json;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserMiddleware implements MiddlewareInterface
{
    /*
     * User格式：{"id":6,"phone_country":86,"phone":"17755185540","last_time":"2022-03-20 11:01:58","last_ip":"127.0.0.1","sharer":[],"tokenType":"user"}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $jwtData = JwtHelper::decodeWithRequest('USER', $request);
        // 如果两个中间件都互相放行，会导致用户不需要登录。所以用户鉴权为必须登录，但放后面。
        // 如果其他中间件有存储用户信息到上下文，则证明其他中间件有校验用户身份，直接放行。若需要两个中间件，user中间件放后面。
        if (! $jwtData && contextGet('nowUser')) {
            return $handler->handle($request);
        }

        // 未登录，或登录状态超过14天
        if (! $jwtData || time() - $jwtData->iat > 86400 * (cfg('user_login_expire_day') ?: 14)) {
            return self::json('请登录！');
        }

        $jwtData->data->tokenType = 'user';

        //将登录信息存储到协程上下文
        contextSet('nowUser', $jwtData->data);
        return $handler->handle($request);
    }

    private static function json(string $msg, int $errCode = ApiHelper::LOGIN_ERROR)
    {
        $body = new SwooleStream(Json::encode(ApiHelper::genErrorData($msg, $errCode)));
        return Context::get(ResponseInterface::class)
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withBody($body);
    }
}

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

use Bailing\Annotation\EnumCodeInterface;
use Bailing\Constants\Code\Common\CommonCode;
use Bailing\Helper\ApiHelper;
use Bailing\Helper\JwtHelper;
use Hyperf\Codec\Json;
use Hyperf\Context\Context;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserMiddleware implements MiddlewareInterface
{
    /**
     * 校验用户登录身份，未携带用户令牌时允许复用前置中间件已认证的身份。
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $jwtData = JwtHelper::decodeWithRequest('USER', $request);
        // 如果两个中间件都互相放行，会导致用户不需要登录。所以用户鉴权为必须登录，但放后面。
        // 如果其他中间件有存储用户信息到上下文，则证明其他中间件有校验用户身份，直接放行。若需要两个中间件，user中间件放后面。
        if ($request->getHeaderLine(JwtHelper::USER_JWT_TOKEN) === '' && contextGet('nowUser')) {
            return $handler->handle($request);
        }

        // 缺失、无效或超时令牌均由请求解析入口返回 null；携带失效令牌不能借用其他身份放行。
        if (! $jwtData) {
            return self::json(CommonCode::NEED_LOGIN);
        }

        $jwtData->data->tokenType = 'user';

        // 将登录信息存储到协程上下文
        contextSet('nowUser', $jwtData->data);
        return $handler->handle($request);
    }

    /** 返回用户认证失败的统一多语言响应，默认使用登录错误码。 */
    private static function json(array|EnumCodeInterface|string $msg, int $errCode = ApiHelper::LOGIN_ERROR)
    {
        $body = new SwooleStream(Json::encode(ApiHelper::genErrorData($msg, $errCode)));
        return Context::get(ResponseInterface::class)
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withBody($body);
    }
}

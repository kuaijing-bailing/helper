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
use Hyperf\Context\Context;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Codec\Json;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DatavMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $tokenType = strtoupper($request->getHeaderLine('token-type'));
        $jwtData = JwtHelper::decodeWithRequest($tokenType, $request);
        // 未登录;不合法
        if (! $jwtData) {
            return self::json(CommonCode::NEED_LOGIN);
        }

        $jwtData->data->tokenType = $tokenType;

        contextSet('nowUser', $jwtData->data); //将登录信息存储到协程上下文
        return $handler->handle($request);
    }

    private static function json(string|array|EnumCodeInterface $msg)
    {
        $body = new SwooleStream(Json::encode(ApiHelper::genErrorData($msg, ApiHelper::LOGIN_ERROR)));
        return Context::get(ResponseInterface::class)
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withBody($body);
    }
}

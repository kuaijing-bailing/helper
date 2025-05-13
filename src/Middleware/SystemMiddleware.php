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
use Bailing\Helper\RequestHelper;
use Bailing\JsonRpc\Publics\SystemMenuServiceInterface;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Codec\Json;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SystemMiddleware implements MiddlewareInterface
{
    private const SUPER_ADMIN_LEVEL = 99; //超级管理员

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $classMethod = explode(':', RequestHelper::getAdminModule());
        $annotations = AnnotationCollector::getClassMethodAnnotation($classMethod[0], $classMethod[1]);
        $jwtData = JwtHelper::decodeWithRequest('SYSTEM', $request);
        $annotationsArr = (array) $annotations;
        if (! $jwtData && isset($annotationsArr['Hyperf\HttpServer\Annotation\Middleware']) && $annotationsMiddleware = (array) $annotationsArr['Hyperf\HttpServer\Annotation\Middleware']) {
            if (! empty($annotationsMiddleware)) {
                $annotationsMiddleware = array_values($annotationsMiddleware);
                array_walk($annotationsMiddleware, function (&$val, $key) {$val = array_unique(array_column((array) $val, 'middleware')); });
            }
            //放行中间件配置项
            $passOtherMiddleware = ['Bailing\Middleware\UserMiddleware', 'Bailing\Middleware\OrgMiddleware'];
            $passAuth = array_intersect($annotationsMiddleware[0], $passOtherMiddleware);
            if (! empty($passAuth)) {
                return $handler->handle($request);
            }
            //针对单个接口继承多个服务中间件鉴权 则只校验本服务token-type的token 其他服务则放行
        }

        // 未登录，或登录状态超过14天
        if (! $jwtData || time() - $jwtData->iat > 86400 * (cfg('system_login_expire_day') ?: 14)) {
            return self::json('请登录！');
        }
        try {
            $redisClient = redis('public');
            if ($redisClient->exists('admin_status_' . $jwtData->data->id) && $redisClient->get('admin_status_' . $jwtData->data->id) == 'deleted') {
                // 用户已被删除   key= admin_status_1(uid) value = deleted
                return self::json('请登录！');
            }
        } catch (\Exception $exception) {
            stdLog()->debug('SYSTEM REDIS CLIENT ERROR', ['module' => RequestHelper::getAdminModule()]);
        }
        try {
            $redisOrgClient = redis('org');
            if ($redisOrgClient->exists('org_user_status_' . $jwtData->data->id) && $redisOrgClient->get('org_user_status_' . $jwtData->data->id) == 'deleted') {
                return self::json('您已被移出该机构!');
            }
        } catch (\Exception $exception) {
            stdLog()->debug('ORG REDIS CLIENT ERROR', ['module' => RequestHelper::getAdminModule()]);
        }

        $jwtData->data->tokenType = 'system';

        if ($jwtData->data->level == self::SUPER_ADMIN_LEVEL) { // 超级管理员
            contextSet('nowUser', $jwtData->data); //将登录信息存储到协程上下文
            unset($jwtData);
            return $handler->handle($request);
        }

        //不经过权限认证
        $classMethod = explode(':', RequestHelper::getAdminModule());
        $annotations = AnnotationCollector::getClassMethodAnnotation($classMethod[0], $classMethod[1]);
        if (! array_key_exists('Bailing\Annotation\SystemPermission', $annotations)) {
            contextSet('nowUser', $jwtData->data); //将登录信息存储到协程上下文
            unset($jwtData);
            return $handler->handle($request);
        }

        $adminRole = $jwtData->data->role_id;
        if ($jwtData->data->id && empty($adminRole)) {
            return self::json('账号异常!未绑定角色身份', ApiHelper::AUTH_ERROR);
        }
        if (! $adminRole || ! $this->allowAccess($jwtData->data->role_id)) {
            return self::json('无权访问', ApiHelper::AUTH_ERROR);
        }
        contextSet('nowUser', $jwtData->data); //将登录信息存储到协程上下文
        unset($jwtData, $adminRole);
        return $handler->handle($request);
    }

    private static function json(string $msg, int $errCode = ApiHelper::LOGIN_ERROR)
    {
        $body = new SwooleStream(Json::encode(ApiHelper::genErrorData($msg, $errCode)));
        return Context::get(ResponseInterface::class)
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withBody($body);
    }

    /**
     * Notes: 判断路由节点访问是否拥有权限(系统后台控制)
     * Author: Endness
     * Date: 2023/5/16 14:31.
     * @return bool
     */
    private function allowAccess(int $role_id)
    {
        if (env('APP_NAME') == 'public' && class_exists('\App\JsonRpc\SystemMenuService')) {
            $orgService = container()->get(\App\JsonRpc\SystemMenuService::class)->getRoleRbacList($role_id);
        } else {
            $orgService = container()->get(SystemMenuServiceInterface::class)->getRoleRbacList($role_id);
        }
        $adminModule = RequestHelper::getAdminModule();
        $rbacAccess = ! empty($orgService['data']['rbacList']) ? $orgService['data']['rbacList'] : [];
        if (in_array($adminModule, $rbacAccess) && ! empty($rbacAccess)) {
            return true;
        }
        return true;
    }
}

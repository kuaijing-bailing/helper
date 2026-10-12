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

use App\JsonRpc\OrgService;
use Bailing\Annotation\AllowUnboundOrg;
use Bailing\Annotation\EnumCodeInterface;
use Bailing\Constants\Code\Common\CommonCode;
use Bailing\Helper\ApiHelper;
use Bailing\Helper\JwtHelper;
use Bailing\Helper\RequestHelper;
use Bailing\JsonRpc\Org\OrgServiceInterface;
use Hyperf\Codec\Json;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class OrgMiddleware implements MiddlewareInterface
{
    private const SUPER_ROLE_LEVEL = 99; // 机构创建者

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
     * Org格式：{"id":1,"phone_country":86,"phone":"1xxxxxxxxxx","last_time":"2023-02-23 14:15:10","last_ip":"127.0.0.1","user_id":1,"name":"管理员","org_id":1,"org_name":"xx楼宇","role_id":1,"level":99,"isSuper":true,"isLayerAdmin":false}
     * Ps：id为user服务id(user服务-user表-id) 用户唯一标识身份id； user_id为所处org用户身份id(org服务-user表-id).
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $classMethod = explode(':', RequestHelper::getAdminModule());
        $annotations = AnnotationCollector::getClassMethodAnnotation($classMethod[0], $classMethod[1]) ?? [];
        $jwtData = JwtHelper::decodeWithRequest('ORG', $request);
        $annotationsArr = (array) $annotations;
        if (! $jwtData && isset($annotationsArr['Hyperf\HttpServer\Annotation\Middleware']) && $annotationsMiddleware = (array) $annotationsArr['Hyperf\HttpServer\Annotation\Middleware']) {
            if (! empty($annotationsMiddleware)) {
                $annotationsMiddleware = array_values($annotationsMiddleware);
                array_walk($annotationsMiddleware, function (&$val, $key) {$val = array_unique(array_column((array) $val, 'middleware')); });
            }
            // 放行中间件配置项
            $passOtherMiddleware = ['Bailing\Middleware\UserMiddleware', 'Bailing\Middleware\SystemMiddleware'];
            $passAuth = array_intersect($annotationsMiddleware[0], $passOtherMiddleware);
            if (! empty($passAuth)) {
                return $handler->handle($request);
            }
            // 针对单个接口继承多个服务中间件鉴权 则只校验本服务token-type的token 其他服务则放行
        }
        if (! $jwtData) { // 未登录，或登录状态超过30分钟
            return self::json(CommonCode::NEED_LOGIN);
        }
        if (time() - $jwtData->iat > 60 * (cfg('org_login_expire_time') ?: 30)) { // 未登录，或登录状态超过30分钟
            return self::json(CommonCode::LOGIN_EXPIRED);
        }

        if (! empty($jwtData->data->intranet_access) && ! RequestHelper::isLocalNetwork()) {
            return self::json(CommonCode::VISIT_NEED_INTRANET->genI18nMsg(['ip' => RequestHelper::getClientIp()]));
        }

        if (! isset($jwtData->data) || ! is_object($jwtData->data) || ! self::isPositiveId($jwtData->data->id ?? null)) {
            return self::json(CommonCode::NEED_LOGIN);
        }

        $jwtData->data->tokenType = 'org';
        $scope = $jwtData->data->scope ?? '';
        $hasOrgIdentity = self::isPositiveId($jwtData->data->org_id ?? null)
            && self::isPositiveId($jwtData->data->user_id ?? null);
        // 必须先校验机构身份，再处理超级管理员和免菜单权限接口，旧的 org_id=0 令牌也受此限制。
        if (! $hasOrgIdentity || $scope !== '') {
            if (! isset($annotations[AllowUnboundOrg::class])
                || ! in_array($scope, ['', JwtHelper::ORG_ONBOARDING_SCOPE], true)) {
                return self::json(CommonCode::AUTH_ERROR, ApiHelper::AUTH_ERROR);
            }
            // 入驻上下文不能携带历史令牌中的角色或超级管理员身份。
            $jwtData->data->org_id = 0;
            $jwtData->data->user_id = 0;
            $jwtData->data->role_id = [];
            $jwtData->data->level = 0;
            $jwtData->data->isSuper = false;
            $jwtData->data->isLayerAdmin = false;
            contextSet('nowUser', $jwtData->data);
            return $handler->handle($request);
        }

        if (isset($jwtData->data->level) && $jwtData->data->level == self::SUPER_ROLE_LEVEL) { // 机构创建者=超级管理员 拥有最高访问权限
            contextSet('nowUser', $jwtData->data); // 将登录信息存储到协程上下文
            unset($jwtData);
            return $handler->handle($request);
        }
        $hasOrgOrderPermission = array_key_exists('Bailing\Annotation\OrgOrderPermission', $annotations);
        $hasOrgPermission = array_key_exists('Bailing\Annotation\OrgPermission', $annotations);
        if ($hasOrgOrderPermission) {
            if (! $hasOrgPermission) {
                contextSet('org_permission_source', 'aspect');
                contextSet('nowUser', $jwtData->data); // 将登录信息存储到协程上下文
                unset($jwtData);
                return $handler->handle($request);
            }
        } elseif (! $hasOrgPermission) {
            contextSet('nowUser', $jwtData->data); // 将登录信息存储到协程上下文
            unset($jwtData);
            return $handler->handle($request);
        }

        $adminRole = $jwtData->data->role_id ?? 0;
        if ($jwtData->data->id && empty($adminRole)) {
            return self::json(CommonCode::NOT_BIND_ROLE, ApiHelper::AUTH_ERROR);
        }
        if (! $adminRole || ! $this->allowAccess($jwtData->data->role_id, $jwtData->data->org_id, $jwtData->data->id)) {
            if ($hasOrgOrderPermission) {
                contextSet('org_permission_source', 'aspect');
                contextSet('nowUser', $jwtData->data); // 将登录信息存储到协程上下文
                unset($jwtData, $adminRole);
                return $handler->handle($request);
            }
            $authName = $this->getAuthName();
            if (! empty($authName)) {
                return self::json(CommonCode::AUTH_ERROR_ACTION->genI18nMsg(['action' => $authName]), ApiHelper::AUTH_ERROR);
            }
            return self::json(CommonCode::AUTH_ERROR, ApiHelper::AUTH_ERROR);
        }
        if ($hasOrgOrderPermission) {
            contextSet('org_permission_source', 'middleware');
        }
        contextSet('nowUser', $jwtData->data); // 将登录信息存储到协程上下文
        unset($jwtData, $adminRole);
        return $handler->handle($request);
    }

    /**
     * 身份主键必须是正整数，拒绝缺失值、布尔值和非数字载荷。
     */
    private static function isPositiveId(mixed $id): bool
    {
        return (is_int($id) || is_string($id))
            && filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false;
    }

    private static function json(array|EnumCodeInterface|string $msg, int $errCode = ApiHelper::LOGIN_ERROR)
    {
        $body = new SwooleStream(Json::encode(ApiHelper::genErrorData($msg, $errCode)));
        return Context::get(ResponseInterface::class)
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withBody($body);
    }

    /**
     * Notes:判断路由节点访问是否拥有权限(机构后台控制)
     * User: Endness
     * Date: 2021/10/11
     * Time: 17:22.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function allowAccess(array|int $roleId, int $orgId, int $userId): bool
    {
        if (env('APP_NAME') == 'org' && class_exists('\App\JsonRpc\OrgService')) {
            $orgService = container()->get(OrgService::class)->getRoleRbacList(is_array($roleId) ? $roleId : [$roleId], $orgId, $userId);
        } else {
            $orgService = container()->get(OrgServiceInterface::class)->getRoleRbacList(is_array($roleId) ? $roleId : [$roleId], $orgId, $userId);
        }
        $adminModule = RequestHelper::getAdminModule();
        $rbacAccess = ! empty($orgService['data']['rbacList']) ? $orgService['data']['rbacList'] : [];
        if (in_array($adminModule, $rbacAccess) && ! empty($rbacAccess)) {
            return true;
        }
        return false;
    }

    /**
     * 获取菜单和权限名称.
     */
    private function getAuthName()
    {
        $adminModule = RequestHelper::getAdminModule();
        if (env('APP_NAME') == 'org' && class_exists('\App\JsonRpc\OrgService')) {
            $authResult = container()->get(OrgService::class)->getMenuAuthName($adminModule);
        } else {
            $authResult = container()->get(OrgServiceInterface::class)->getMenuAuthName($adminModule);
        }
        return ! empty($authResult['data']['name']) ? $authResult['data']['name'] : '';
    }
}

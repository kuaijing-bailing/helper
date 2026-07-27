<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */

namespace Bailing\Aspect;

use App\JsonRpc\OrgService;
use Bailing\Annotation\OrgOrderPermission;
use Bailing\Constants\Code\Common\CommonCode;
use Bailing\Helper\AesHelper;
use Bailing\Helper\ApiHelper;
use Bailing\JsonRpc\Org\OrgServiceInterface;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

class OrgOrderPermissionAspect extends AbstractAspect
{
    private const SUPER_ROLE_LEVEL = 99;

    private const CACHE_TTL = 600;

    public array $classes = [];

    public array $annotations = [
        OrgOrderPermission::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $annotation = $this->getAnnotation($proceedingJoinPoint);
        if (! $annotation) {
            return $proceedingJoinPoint->process();
        }

        $nowUser = contextGet('nowUser');
        if (empty($nowUser)) {
            return ApiHelper::genErrorData(CommonCode::NEED_LOGIN, ApiHelper::LOGIN_ERROR);
        }

        if (! empty($nowUser->isSuper) || ((int) ($nowUser->level ?? 0) === self::SUPER_ROLE_LEVEL)) {
            return $proceedingJoinPoint->process();
        }

        $orgId = (int) ($nowUser->org_id ?? request()->input('org_id', 0));
        $userId = (int) ($nowUser->id ?? 0);
        $roleId = $nowUser->role_id ?? 0;
        if (empty($orgId) || empty($userId) || empty($roleId)) {
            return ApiHelper::genErrorData(CommonCode::AUTH_ERROR, ApiHelper::AUTH_ERROR);
        }

        $permissionKey = $this->buildPermissionKey($proceedingJoinPoint, $annotation);
        if (empty($permissionKey)) {
            return ApiHelper::genErrorData(CommonCode::PARAM_ERROR);
        }

        $rbacAccess = $this->getRbacAccess($roleId, $orgId, $userId);
        if (! in_array($permissionKey, $rbacAccess, true)) {
            return ApiHelper::genErrorData(CommonCode::AUTH_ERROR, ApiHelper::AUTH_ERROR);
        }

        return $proceedingJoinPoint->process();
    }

    private function getAnnotation(ProceedingJoinPoint $proceedingJoinPoint): ?OrgOrderPermission
    {
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();
        foreach ($metadata->method as $annotation) {
            if ($annotation instanceof OrgOrderPermission) {
                return $annotation;
            }
        }

        foreach ($metadata->class as $annotation) {
            if ($annotation instanceof OrgOrderPermission) {
                return $annotation;
            }
        }

        return null;
    }

    private function buildPermissionKey(ProceedingJoinPoint $proceedingJoinPoint, OrgOrderPermission $annotation): string
    {
        $alias = $this->resolveTemplate($annotation->alias);
        if ($alias === '') {
            return '';
        }

        return $proceedingJoinPoint->className . ':' . $proceedingJoinPoint->methodName . ucfirst($alias);
    }

    private function resolveTemplate(string $template): string
    {
        $hasEmptyParam = false;
        $resolvedTemplate = preg_replace_callback('/\{([A-Za-z0-9_]+)\}/', function (array $matches) use (&$hasEmptyParam) {
            $value = (string) $this->getRequestValue($matches[1], '');
            if ($value === '') {
                $hasEmptyParam = true;
            }
            return ucfirst($value);
        }, $template) ?? '';

        return $hasEmptyParam ? '' : $resolvedTemplate;
    }

    private function getRequestValue(string $key, mixed $default = ''): mixed
    {
        $value = request()->query($key, null);
        if ($value === null) {
            $value = request()->input($key, $default);
        }

        return is_scalar($value) ? $value : $default;
    }

    private function getRbacAccess(array|int $roleId, int $orgId, int $userId): array
    {
        $roleIdArr = is_array($roleId) ? $roleId : [$roleId];
        $cacheKey = sprintf('org_order_permission_rbac:%s:%s:%s', $orgId, $userId, AesHelper::digest(serialize($roleIdArr)));
        $cacheResult = cacheData($cacheKey, function () use ($roleIdArr, $orgId, $userId) {
            if (env('APP_NAME') == 'org' && class_exists('\App\JsonRpc\OrgService')) {
                $result = container()->get(OrgService::class)->getRoleRbacList($roleIdArr, $orgId, $userId);
            } else {
                $result = container()->get(OrgServiceInterface::class)->getRoleRbacList($roleIdArr, $orgId, $userId);
            }

            if (! ApiHelper::checkDataOk($result)) {
                return null;
            }

            return [
                'rbacList' => $result['data']['rbacList'] ?? [],
            ];
        }, self::CACHE_TTL);

        if (empty($cacheResult) || ! isset($cacheResult['rbacList']) || ! is_array($cacheResult['rbacList'])) {
            return [];
        }

        return array_map('strval', $cacheResult['rbacList']);
    }
}

<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */

namespace Bailing\Controller;

use Bailing\Annotation\RateRequest;
use Bailing\Helper\ApiHelper;
use Bailing\Helper\OrgConfigHelper;
use Bailing\Helper\StrHelper;
use Bailing\Middleware\OrgMiddleware;
use Bailing\Middleware\SystemMiddleware;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PutMapping;

#[Controller]
class OrgConfigController
{
    #[GetMapping(path: '/common/orgConfig/get')]
    #[Middleware(SystemMiddleware::class)]
    public function get(): array
    {
        $orgId = intval(request()->input('org_id', 0));
        $name = StrHelper::mb_trim(strval(request()->input('name', '')));
        $index = StrHelper::mb_trim(strval(request()->input('index', '')));

        if (empty($orgId) || empty($name)) {
            return ApiHelper::genErrorData('param[org_id, name] can not empty');
        }

        $result = OrgConfigHelper::getConfig($orgId, $name, $index);

        return ApiHelper::genSuccessData(['result' => $result]);
    }

    #[PutMapping(path: '/common/orgConfig/set')]
    #[Middleware(SystemMiddleware::class)]
    #[RateRequest]
    public function set(): array
    {
        $orgId = intval(request()->input('org_id', 0));
        $name = StrHelper::mb_trim(strval(request()->input('name', '')));
        $value = StrHelper::mb_trim(strval(request()->input('value', '')));
        $index = StrHelper::mb_trim(strval(request()->input('index', '')));

        if (empty($orgId) || empty($name) || empty($value)) {
            return ApiHelper::genErrorData('param[org_id, name, value] can not empty');
        }

        $result = OrgConfigHelper::setConfig($orgId, $name, $value, $index);

        return ApiHelper::genSuccessData(['result' => $result]);
    }

    #[GetMapping(path: '/org/common/orgConfig/get')]
    #[Middleware(OrgMiddleware::class)]
    public function getOrg(): array
    {
        $nowAdmin = contextGet('nowUser');
        $orgId = $nowAdmin->org_id;

        $name = StrHelper::mb_trim(strval(request()->input('name', '')));
        $index = StrHelper::mb_trim(strval(request()->input('index', '')));

        if (empty($orgId) || empty($name)) {
            return ApiHelper::genErrorData('param[name] can not empty');
        }

        $result = OrgConfigHelper::getConfig($orgId, $name, $index);

        return ApiHelper::genSuccessData(['result' => $result]);
    }

    #[PutMapping(path: '/org/common/orgConfig/set')]
    #[Middleware(OrgMiddleware::class)]
    #[RateRequest]
    public function setOrg(): array
    {
        $nowAdmin = contextGet('nowUser');
        $orgId = $nowAdmin->org_id;

        $name = StrHelper::mb_trim(strval(request()->input('name', '')));
        $value = StrHelper::mb_trim(strval(request()->input('value', '')));
        $index = StrHelper::mb_trim(strval(request()->input('index', '')));

        if (empty($orgId) || empty($name) || empty($value)) {
            return ApiHelper::genErrorData('param[name, value] can not empty');
        }

        $result = OrgConfigHelper::setConfig($orgId, $name, $value, $index);

        return ApiHelper::genSuccessData(['result' => $result]);
    }
}

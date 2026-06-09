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
use Bailing\Middleware\SystemMiddleware;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PutMapping;

#[Controller]
#[Middleware(SystemMiddleware::class)]
class OrgConfigController
{
    #[GetMapping(path: '/common/orgConfig/get')]
    public function get(): array
    {
        $orgId = request()->input('org_id');
        $name = request()->input('name');
        $index = request()->input('index', '');

        if (! isset($orgId) || $orgId === '' || ! isset($name) || $name === '') {
            return ApiHelper::genErrorData('org_id 和 name 参数必传');
        }

        $result = OrgConfigHelper::getConfig((int) $orgId, StrHelper::mb_trim((string) $name), StrHelper::mb_trim((string) $index));

        return ApiHelper::genSuccessData(['result' => $result]);
    }

    #[PutMapping(path: '/common/orgConfig/set')]
    #[RateRequest]
    public function set(): array
    {
        $orgId = request()->input('org_id');
        $name = request()->input('name');
        $value = request()->input('value');
        $index = request()->input('index', '');

        if (! isset($orgId) || $orgId === '' || ! isset($name) || $name === '' || ! isset($value)) {
            return ApiHelper::genErrorData('org_id、name 和 value 参数必传');
        }

        $result = OrgConfigHelper::setConfig((int) $orgId, StrHelper::mb_trim((string) $name), StrHelper::mb_trim((string) $value), StrHelper::mb_trim((string) $index));

        return ApiHelper::genSuccessData(['result' => $result]);
    }
}

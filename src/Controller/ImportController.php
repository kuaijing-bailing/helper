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

use Bailing\Helper\ApiHelper;
use Bailing\Helper\OrgConfigHelper;
use Bailing\Middleware\OrgMiddleware;
use Hyperf\Codec\Json;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;

#[Controller]
class ImportController
{
    #[GetMapping('/export/config')]
    #[Middleware(OrgMiddleware::class)]
    public function exportConfig(): array
    {
        $alias = request()->input('alias');
        if (empty($alias)) {
            return ApiHelper::genErrorData('缺少参数alias');
        }
        $nowAdmin = contextGet('nowUser');

        $config = OrgConfigHelper::getConfig($nowAdmin->org_id, $alias);
        if (empty($config)) {
            return ApiHelper::genErrorData('配置不存在');
        }
        $configArr = Json::decode($config);

        $list[] = [
            'field' => $configArr['field_name'],
            'name' => $configArr['show_name'],
        ];

        return ApiHelper::genSuccessData(['list' => $list]);
    }
}

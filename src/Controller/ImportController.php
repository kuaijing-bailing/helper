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

use Bailing\Constants\Code\Common\CommonCode;
use Bailing\Helper\ApiHelper;
use Bailing\Helper\Intl\DateTimeHelper;
use Bailing\Office\Collection;
use Hyperf\Codec\Json;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Psr\Http\Message\ResponseInterface;

#[Controller]
class ImportController
{
    /**
     * 导入失败的返回.
     */
    #[GetMapping(path: '/system/import/error')]
    public function importError(): array|ResponseInterface
    {
        $errorFileKey = request()->input('errorFileKey', '');
        if (empty($errorFileKey)) {
            return ApiHelper::genErrorData(CommonCode::IMPORT_FILE_ID_EMPTY);
        }

        $cacheResult = redis()->get($errorFileKey);
        if (empty($cacheResult)) {
            return ApiHelper::genErrorData(CommonCode::IMPORT_FILE_EXPIRED);
        }
        $cacheResultArr = Json::decode($cacheResult);
        if (empty($cacheResultArr['dto'])) {
            return ApiHelper::genErrorData(CommonCode::IMPORT_FILE_EXPIRED);
        }

        return (new Collection())->export(
            dto: $cacheResultArr['dto'],
            filename: $cacheResultArr['filename'] . '-' . DateTimeHelper::getFormatDate(time()),
            closure: $cacheResultArr['data'] ?? [],
            orgId: $cacheResultArr['org_id'],
        );
    }
}

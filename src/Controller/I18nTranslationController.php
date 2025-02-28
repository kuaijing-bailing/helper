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
use Bailing\Helper\Intl\I18nTranslationHelper;
use Bailing\Middleware\SystemMiddleware;
use Bailing\Model\BailingI18nTranslation;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;

#[Controller]
class I18nTranslationController
{
    #[PostMapping(path: '/system/i18n/translation/query')]
    #[Middleware(SystemMiddleware::class)]
    public function serviceI18nTranslationList(): array
    {
        $post = request()->all();

        // 第一页模拟创建下表，避免报错
        if(empty($post['page']) || $post['page'] == 1){
            I18nTranslationHelper::createTable();
        }

        // 默认为ID倒序
        if (empty($post['sorts'])) {
            $post['sorts'] = [
                [
                    'field' => 'id',
                    'asc' => false,
                ],
            ];
        }
        $list = buildFormSearchQuery(BailingI18nTranslation::query(), $post['filters'], $post['sorts'])->paginate((int) ($post['pageSize'] ?? 20))->toArray();

        return ApiHelper::genSuccessData(genListData($list));
    }

    #[PutMapping(path: '/system/i18n/translation/{id:\d+}')]
    #[Middleware(SystemMiddleware::class)]
    public function serviceI18nTranslationSave(?int $id): array
    {
        $nowAdmin = contextGet('nowUser');
        if (empty($nowAdmin)) {
            return ApiHelper::genErrorData('Please login');
        }

        $post = request()->all();
        $value = $post['value'];

        if (empty($value)) {
            return ApiHelper::genErrorData('Missing important fields');
        }
        $i18nTranslation = BailingI18nTranslation::query()->where('id', $id)->first();
        if(empty($i18nTranslation)){
            return ApiHelper::genErrorDataEmpty();
        }
        $i18nTranslation->value = $value;
        $i18nTranslation->is_changed = 1;
        $i18nTranslation->save();

        return ApiHelper::genSuccessData(['id' => $i18nTranslation->id]);
    }
}

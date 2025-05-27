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
use Bailing\Helper\TranslationHelper;
use Bailing\Middleware\OrgMiddleware;
use Bailing\Middleware\SystemMiddleware;
use Bailing\Model\BailingTranslation;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;

#[Controller]
class LanguageController
{
    #[PutMapping(path: '/system/language')]
    public function languageSave(): array
    {
        if (! isDevEnv()) {
            return ApiHelper::genErrorData('非开发环境不允许修改语言');
        }
        $langList = cfg('lang_list');
        if (empty($langList)) {
            return ApiHelper::genErrorData('语言环境为空，无法修改');
        }
        $langListArr = explode(',', $langList);

        $text = request()->input('text');
        $valueArr = request()->input('values');

        if (! $text || ! $valueArr) {
            return ApiHelper::genErrorData('参数错误');
        }

        $textArr = explode('.', $text);
        if (count($textArr) < 2) {
            return ApiHelper::genErrorData('文件名必传，例如： message.tip');
        }
        if (count($textArr) > 2) {
            return ApiHelper::genErrorData('多语言键只支持2级');
        }

        foreach ($valueArr as $key => $item) {
            if (in_array($key, $langListArr)) {
                $tmpDir = config('translation.path') . '/' . $key;
                if (! is_dir($tmpDir)) {
                    mkdir($tmpDir, 0777, true);
                }
                $tmpFile = $tmpDir . '/' . $textArr[0] . '.php';
                if (file_exists($tmpFile)) {
                    $lang = include $tmpFile;
                    $lang[$textArr[1]] = $item;

                    file_put_contents($tmpFile, '<?php' . PHP_EOL . 'return ' . var_export($lang, true) . ';');
                } else {
                    file_put_contents($tmpFile, '<?php' . PHP_EOL . 'return [ ' . PHP_EOL . ' \'' . $textArr[1] . '\' => \'' . $item . '\' ' . PHP_EOL . '];');
                }
            }
        }

        return ApiHelper::genSuccessData([], '操作成功');
    }

    #[PostMapping(path: '/system/data/translation/query')]
    #[Middleware(SystemMiddleware::class)]
    public function serviceTranslationList(): array
    {
        $post = request()->all();
        // 默认为ID倒序
        if (empty($post['sorts'])) {
            $post['sorts'] = [
                [
                    'field' => 'id',
                    'asc' => false,
                ],
            ];
        }
        $list = buildFormSearchQuery(BailingTranslation::query(), $post['filters'], $post['sorts'])->paginate((int) request()->input('pageSize', 20))->toArray();

        return ApiHelper::genSuccessData(genListData($list));
    }

    #[PutMapping(path: '/system/data/translation')]
    #[Middleware(SystemMiddleware::class)]
    #[Middleware(OrgMiddleware::class)]
    public function serviceTranslationSave(): array
    {
        $nowAdmin = contextGet('nowUser');
        if (empty($nowAdmin)) {
            return ApiHelper::genErrorData('Please login');
        }

        $post = request()->all();
        $dataId = $post['data_id'];
        $tableField = $post['table_field'];
        $value = $post['value'];

        if (empty($tableField) || empty($value)) {
            return ApiHelper::genErrorData('Missing important fields');
        }
        if (! TranslationHelper::saveTranslation($nowAdmin->tokenType == 'system' ? 0 : $nowAdmin->org_id, $tableField, $dataId, $tableField, $value)) {
            return ApiHelper::genErrorData('Save error');
        }

        return ApiHelper::genSuccessData([]);
    }
}

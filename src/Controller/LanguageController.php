<?php

namespace Bailing\Controller;

use Bailing\Helper\ApiHelper;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PutMapping;

#[Controller]
class LanguageController
{
    #[PutMapping(path: '/system/language')]
    public function languageSave(): array
    {
        if (env('APP_ENV') != 'dev') {
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
}
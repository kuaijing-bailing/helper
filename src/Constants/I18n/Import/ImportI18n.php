<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\Constants\I18n\Import;

use Bailing\Annotation\EnumI18n;
use Bailing\Annotation\EnumI18nGroup;
use Bailing\Annotation\EnumI18nInterface;
use Bailing\Trait\EnumI18nGet;

#[EnumI18nGroup(groupCode: 'ImportI18n', info: '导入类')]
enum ImportI18n: int implements EnumI18nInterface
{
    use EnumI18nGet;

    #[EnumI18n(txt: '文件名称', i18nTxt: ['en' => 'File Name', 'zh_tw' => '文件名稱', 'ja' => 'ファイル名前', 'zh_hk' => '文件名稱'])]
    case FILE_NAME = 1;

    #[EnumI18n(txt: '导入时间', i18nTxt: ['en' => 'Import Time', 'zh_tw' => '導入時間', 'ja' => 'インポート時間', 'zh_hk' => '導入時間'])]
    case IMPORT_TIME = 2;

    #[EnumI18n(txt: '导入失败数量', i18nTxt: ['en' => 'Import failed count', 'zh_tw' => '導入失敗數量', 'ja' => 'インポート失敗数', 'zh_hk' => '導入失敗數量'])]
    case IMPORT_FAILED_COUNT = 4;

    #[EnumI18n(txt: '导入结果通知', i18nTxt: ['en' => 'Import result notification', 'zh_tw' => '導入結果通知', 'ja' => 'インポート結果通知', 'zh_hk' => '導入結果通知'])]
    case IMPORT_RESULT_NOTIFICATION = 5;

    #[EnumI18n(txt: '导入成功数量', i18nTxt: ['en' => 'Import success count', 'zh_tw' => '導入成功數量', 'ja' => 'インポート成功数', 'zh_hk' => '導入成功數量'])]
    case IMPORT_SUCCESS_COUNT = 6;

    #[EnumI18n(txt: '字段（{field}）日期时间格式错误', i18nTxt: ['en' => 'Field ({field}) date time format error', 'zh_tw' => '欄位（{field}）日期時間格式錯誤', 'ja' => 'フィールド（{field}）日付時刻形式エラー', 'zh_hk' => '欄位（{field}）日期時間格式錯誤'])]
    case IMPORT_DATE_FORMAT_ERROR = 7;

    #[EnumI18n(txt: '日期时间格式错误', i18nTxt: ['en' => 'Date time format error', 'zh_tw' => '日期時間格式錯誤', 'ja' => '日付時刻形式エラー', 'zh_hk' => '日期時間格式錯誤'])]
    case IMPORT_DATE_ERROR = 8;
}

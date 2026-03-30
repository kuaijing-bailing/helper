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

    #[EnumI18n(txt: '资产导入失败结果，请下载文件查看失败详情，失败0条则没有详情', i18nTxt: ['en' => 'Asset import failed result, please download file to view details, if failed 0 rows, then no details', 'zh_tw' => '資產導入失敗結果，請下載文件查看失敗詳情，失敗0條則沒有詳情', 'ja' => 'アセットインポート失敗結果，詳細を表示してダウンロードファイル，失敗0條則沒有詳情', 'zh_hk' => '資產導入失敗結果，請下載文件查看失敗詳情，失敗0條則沒有詳情'])]
    case IMPORT_RESULT = 3;

    #[EnumI18n(txt: '导入失败数量', i18nTxt: ['en' => 'Import failed count', 'zh_tw' => '導入失敗數量', 'ja' => 'インポート失敗数', 'zh_hk' => '導入失敗數量'])]
    case IMPORT_FAILED_COUNT = 4;

    #[EnumI18n(txt: '资产导入结果通知', i18nTxt: ['en' => 'Asset import result notification', 'zh_tw' => '資產導入結果通知', 'ja' => 'アセットインポート結果通知', 'zh_hk' => '資產導入結果通知'])]
    case IMPORT_RESULT_NOTIFICATION = 5;

    #[EnumI18n(txt: '导入成功数量', i18nTxt: ['en' => 'Import success count', 'zh_tw' => '導入成功數量', 'ja' => 'インポート成功数', 'zh_hk' => '導入成功數量'])]
    case IMPORT_SUCCESS_COUNT = 6;
}

<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\Constants\I18n\Common;

use Bailing\Annotation\EnumI18n;
use Bailing\Annotation\EnumI18nGroup;
use Bailing\Annotation\EnumI18nInterface;
use Bailing\Trait\EnumI18nGet;

#[EnumI18nGroup(groupCode: 'common', info: '公共类')]
enum CommonI18n: int  implements EnumI18nInterface
{
    use EnumI18nGet;

    #[EnumI18n(txt: '导入结果', i18nTxt: ['en' => 'Import Result', 'zh_tw' => '導入結果', 'zh_hk' => '導入結果', 'ja' => '導入結果'])]
    case IMPORT_RESULT = 1;
    #[EnumI18n(txt: '提示：', i18nTxt: ['en' => 'Tip:', 'zh_tw' => '提示：', 'zh_hk' => '提示：', 'ja' => '提示：'])]
    case TIP = 2;
    #[EnumI18n(txt: '请不要修改表结构。', i18nTxt: ['en' => 'Please do not modify the table structure.', 'zh_tw' => '請不要修改表結構。', 'zh_hk' => '請不要修改表結構。', 'ja' => '請不要修改表結構。'])]
    case DONT_MODIFY_TABLE_STRUCTURE = 3;
    #[EnumI18n(txt: '红色字段是必填项，黑色字段是选填项。', i18nTxt: ['en' => 'Red fields are required, black fields are optional.', 'zh_tw' => '紅色欄位是必填項，黑色欄位是選填項。', 'zh_hk' => '紅色欄位是必填項，黑色欄位是選填項。', 'ja' => '紅色欄位是必填項，黑色欄位是選填項。'])]
    case RED_FIELDS_REQUIRED = 4;

	#[EnumI18n(txt: '资产导入失败结果，请下载文件查看失败详情，失败0条则没有详情', i18nTxt: ['en' => 'Asset import failed result, please download file to view details, if failed 0 rows, then no details', 'zh_tw' => '資產導入失敗結果，請下載文件查看失敗詳情，失敗0條則沒有詳情', 'ja' => 'アセットインポート失敗結果，詳細を表示してダウンロードファイル，失敗0條則沒有詳情', 'zh_hk' => '資產導入失敗結果，請下載文件查看失敗詳情，失敗0條則沒有詳情'])]
	case IMPORT_REMARK = 5;
}

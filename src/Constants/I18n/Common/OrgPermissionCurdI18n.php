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

use Bailing\Annotation\EnumCode;
use Bailing\Annotation\EnumI18n;
use Bailing\Annotation\EnumI18nGroup;
use Bailing\Annotation\EnumI18nInterface;
use Bailing\Trait\EnumI18nGet;

#[EnumI18nGroup(groupCode: 'org_permission_curd', info: 'CURD的翻译类')]
enum OrgPermissionCurdI18n: int  implements EnumI18nInterface
{
    use EnumI18nGet;

    #[EnumI18n(txt: '列表', i18nTxt: ['en' => 'List', 'zh_tw' => '清單', 'zh_hk' => '清單', 'ja' => 'リスト'])]
    case LIST = 1;

    #[EnumI18n(txt: '新增', i18nTxt: ['en' => 'Add', 'zh_tw' => '新增', 'zh_hk' => '新增', 'ja' => '追加した'])]
    case ADD = 2;

    #[EnumI18n(txt: '编辑', i18nTxt: ['en' => 'Edit', 'zh_tw' => '編輯', 'zh_hk' => '編輯', 'ja' => '編集'])]
    case EDIT = 3;

    #[EnumI18n(txt: '删除', i18nTxt: ['en' => 'Delete', 'zh_tw' => '刪除', 'zh_hk' => '刪除', 'ja' => '消去'])]
    case DELETE = 4;

    #[EnumI18n(txt: '详情', i18nTxt: ['en' => 'Detail', 'zh_tw' => '詳細', 'zh_hk' => '詳細', 'ja' => '詳細'])]
    case DETAIL = 5;
}

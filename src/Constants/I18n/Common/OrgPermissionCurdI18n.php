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

    #[EnumI18n(txt: '导出', i18nTxt: ['en' => 'Export', 'zh_tw' => '匯出', 'zh_hk' => '匯出', 'ja' => '輸出'])]
    case EXPORT = 6;

    #[EnumI18n(txt: '导入', i18nTxt: ['en' => 'Import', 'zh_tw' => '匯入', 'zh_hk' => '匯入', 'ja' => '輸入'])]
    case IMPORT = 7;

    #[EnumI18n(txt: '字典设置', i18nTxt: ['en' => 'Dict', 'zh_tw' => '字典設定', 'zh_hk' => '字典設定', 'ja' => '辞書設定'])]
    case DICT = 8;

    #[EnumI18n(txt: '审批流设置', i18nTxt: ['en' => 'Approval Setting', 'zh_tw' => '審批流設定', 'zh_hk' => '審批流設定', 'ja' => '承認フロー設定'])]
    case APPROVAL = 9;

    #[EnumI18n(txt: '变更状态', i18nTxt: ['en' => 'Change Status', 'zh_tw' => '變更狀態', 'zh_hk' => '變更狀態', 'ja' => 'ステータスを変更する'])]
    case CHANGE_STATUS = 10;

    #[EnumI18n(txt: '排序', i18nTxt: ['en' => 'Change Sort', 'zh_tw' => '排序', 'zh_hk' => '排序', 'ja' => 'ソートを変更する'])]
    case CHANGE_SORT = 11;

    #[EnumI18n(txt: '编码规则', i18nTxt: ['en' => 'Number Rule', 'zh_tw' => '編碼規則', 'zh_hk' => '編碼規則', 'ja' => '番号のルール'])]
    case NUMBER_RULE = 12;
}

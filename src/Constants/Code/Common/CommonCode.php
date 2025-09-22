<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\Constants\Code\Common;

use Bailing\Annotation\EnumCode;
use Bailing\Annotation\EnumCodeInterface;
use Bailing\Annotation\EnumCodePrefix;
use Bailing\Trait\EnumCodeGet;

#[EnumCodePrefix(prefixCode: 100, info: '公共错误')]
enum CommonCode: int implements EnumCodeInterface
{
    use EnumCodeGet;

    #[EnumCode(msg: '请登录', i18nMsg: ['en' => 'Please log in', 'zh_tw' => '請登入', 'zh_hk' => '請登入', 'ja' => 'ログインしてください'])]
    case NEED_LOGIN = 1;

    #[EnumCode(msg: '登录状态已过期，请重新登录！', i18nMsg: ['en' => 'Login status has expired, please log in again!', 'zh_tw' => '登入狀態已過期，請重新登入！', 'zh_hk' => '登入狀態已過期，請重新登入！', 'ja' => 'ログイン状態が期限切れになりました。再ログインしてください！'])]
    case LOGIN_EXPIRED = 2;

    #[EnumCode(msg: '参数错误', i18nMsg: ['en' => 'Parameter error', 'zh_tw' => '參數錯誤', 'zh_hk' => '參數錯誤', 'ja' => 'パラメータエラー'])]
    case PARAM_ERROR = 3;

    #[EnumCode(msg: '用户信息不存在,请重新注册登录！', i18nMsg: ['en' => 'User information does not exist, please register and log in again!', 'zh_tw' => '用戶信息不存在,請重新註冊登入！', 'zh_hk' => '用戶信息不存在,請重新註冊登入！', 'ja' => 'ユーザー情報が存在しません。再登録してログインしてください！'])]
    case USER_NOT_EXITS = 4;

    #[EnumCode(msg: '您已被移出该机构!', i18nMsg: ['en' => 'You have been removed from the organization!', 'zh_tw' => '您已被移出該機構!', 'zh_hk' => '您已被移出該機構!', 'ja' => 'あなたは組織から外されました！'])]
    case USER_NOT_IN_ORG = 5;

    #[EnumCode(msg: '需要内网才能访问该接口（当前IP：{ip}）', i18nMsg: ['en' => 'Need intranet to access this interface (current IP: {ip})', 'zh_tw' => '需要內網才能訪問該接口（當前IP：{ip}）', 'zh_hk' => '需要內網才能訪問該接口（當前IP：{ip}）', 'ja' => 'インタフェースにアクセスするにはイントラネットが必要です（現在のIP:{ip}）'])]
    case VISIT_NEED_INTRANET = 6;

    #[EnumCode(msg: '账号异常!未绑定角色身份', i18nMsg: ['en' => 'Account abnormal! Not bound to role identity', 'zh_tw' => '帳號異常!未綁定角色身份', 'zh_hk' => '帳號異常!未綁定角色身份', 'ja' => 'アカウント異常!役割のアイデンティティがバインドされていません'])]
    case NOT_BIND_ROLE = 7;

    #[EnumCode(msg: '无权访问', i18nMsg: ['en' => 'No authority to access', 'zh_tw' => '無權訪問', 'zh_hk' => '無權訪問', 'ja' => '権限がありません'])]
    case AUTH_ERROR = 8;

    #[EnumCode(msg: '无权访问（{action}）', i18nMsg: ['en' => 'No authority to access ({action})', 'zh_tw' => '無權訪問（{action}）', 'zh_hk' => '無權訪問（{action}）', 'ja' => '権限がありません（{action}）'])]
    case AUTH_ERROR_ACTION = 9;

    #[EnumCode(msg: '保存失败，请重试', i18nMsg: ['en' => 'Failed to save, please try again', 'zh_tw' => '儲存失敗，請重試', 'zh_hk' => '儲存失敗，請重試', 'ja' => '保存に失敗しました。再試行してください'])]
    case SAVE_FAILED = 10;

    #[EnumCode(msg: '保存成功', i18nMsg: ['en' => 'Saved successfully', 'zh_tw' => '儲存成功', 'zh_hk' => '儲存成功', 'ja' => '保存に成功しました'])]
    case SAVE_SUCCESS = 11;

    #[EnumCode(msg: '操作成功', i18nMsg: ['en' => 'Operation successful', 'zh_tw' => '操作成功', 'zh_hk' => '操作成功', 'ja' => '操作に成功しました'])]
    case OPERATION_SUCCESS = 12;

    #[EnumCode(msg: '操作失败，请重试', i18nMsg: ['en' => 'Operation failed, please try again', 'zh_tw' => '操作失敗，請重試', 'zh_hk' => '操作失敗，請重試', 'ja' => '操作に失敗しました。再試行してください'])]
    case OPERATION_FAILED = 13;

    #[EnumCode(msg: '退出成功', i18nMsg: ['en' => 'Logout successful', 'zh_tw' => '退出成功，請重試', 'zh_hk' => '退出成功，請重試', 'ja' => 'ログアウトに成功しました'])]
    case LOGOUT_SUCCESS = 14;

    #[EnumCode(msg: '退出失败，请重试', i18nMsg: ['en' => 'Logout failed, please try again', 'zh_tw' => '退出失敗，請重試', 'zh_hk' => '退出失敗，請重試', 'ja' => 'ログアウトに失敗しました。再試行してください'])]
    case LOGOUT_FAILED = 15;

    #[EnumCode(msg: '账号异常，请重新登录', i18nMsg: ['en' => 'Account abnormal, please log in again', 'zh_tw' => '帳號異常，請重新登入', 'zh_hk' => '帳號異常，請重新登入', 'ja' => 'アカウント異常です。再ログインしてください'])]
    case ACCOUNT_ABNORMAL = 16;

    #[EnumCode(msg: '导入成功', i18nMsg: ['en' => 'Import successful', 'zh_tw' => '導入成功', 'zh_hk' => '導入成功', 'ja' => 'インポートに成功しました'])]
    case IMPORT_SUCCESS = 17;

    #[EnumCode(msg: '导入失败，请重试', i18nMsg: ['en' => 'Import failed, please try again', 'zh_tw' => '導入失敗，請重試', 'zh_hk' => '導入失敗，請重試', 'ja' => 'インポートに失敗しました。再試行してください'])]
    case IMPORT_FAILED = 18;

    #[EnumCode(msg: '导出成功', i18nMsg: ['en' => 'Export successful', 'zh_tw' => '導出成功', 'zh_hk' => '導出成功', 'ja' => 'エクスポートに成功しました'])]
    case EXPORT_SUCCESS = 19;

    #[EnumCode(msg: '导出失败，请重试', i18nMsg: ['en' => 'Export failed, please try again', 'zh_tw' => '導出失敗，請重試', 'zh_hk' => '導出失敗，請重試', 'ja' => 'エクスポートに失敗しました。再試行してください'])]
    case EXPORT_FAILED = 20;

    #[EnumCode(msg: '未查询到该条数据，请检查该数据是否存在', i18nMsg: ['en' => 'The data was not found. Please check whether the data exists.', 'zh_tw' => '未查詢到該條數據，請檢查該數據是否存在', 'zh_hk' => '未查詢到該條數據，請檢查該數據是否存在', 'ja' => '該データが見つかりません。データが存在するか確認してください'])]
    case DATA_NOT_FOUND = 21;

    #[EnumCode(msg: '提交审批成功', i18nMsg: ['en' => 'Submit approval successful', 'zh_tw' => '提交審批成功', 'zh_hk' => '提交審批成功', 'ja' => '申請に成功しました'])]
    case APPROVAL_SUBMIT_SUCCESS = 22;

    #[EnumCode(msg: '提交审批失败，请重试', i18nMsg: ['en' => 'Submit approval failed, please try again', 'zh_tw' => '提交審批失敗，請重試', 'zh_hk' => '提交審批失敗，請重試', 'ja' => '申請に失敗しました。再試行してください'])]
    case APPROVAL_SUBMIT_FAILED = 23;

    #[EnumCode(msg: '提交成功', i18nMsg: ['en' => 'Submit successful', 'zh_tw' => '提交成功', 'zh_hk' => '提交成功', 'ja' => '提交に成功しました'])]
    case SUBMIT_SUCCESS = 24;

    #[EnumCode(msg: '提交失败，请重试', i18nMsg: ['en' => 'Submit failed, please try again', 'zh_tw' => '提交失敗，請重試', 'zh_hk' => '提交失敗，請重試', 'ja' => '提交に失敗しました。再試行してください'])]
    case SUBMIT_FAILED = 25;

    #[EnumCode(msg: '服务异常（{service_name}）[{error_msg}]', i18nMsg: ['en' => 'Service exception ({service_name}) [{error_msg}]', 'zh_tw' => '服務異常（{service_name}）[{error_msg}]', 'zh_hk' => '服務異常（{service_name}）[{error_msg}]', 'ja' => 'サービス異常（{service_name}）[{error_msg}]'])]
    case SERVICE_EXCEPTION = 26;

    #[EnumCode(msg: '请上传文件', i18nMsg: ['en' => 'Please upload a file', 'zh_tw' => '請上傳文件', 'zh_hk' => '請上傳文件', 'ja' => 'ファイルをアップロードしてください'])]
    case UPLOAD_FILE_EMPTY = 27;

    #[EnumCode(msg: '文件格式（{file_ext}）不允许，只允许（{allow_ext}）', i18nMsg: ['en' => 'File format ({file_ext}) is not allowed, only allow ({allow_ext})', 'zh_tw' => '文件格式（{file_ext}）不允许，只允許（{allow_ext}）', 'zh_hk' => '文件格式（{file_ext}）不允许，只允許（{allow_ext}）', 'ja' => 'ファイル形式（{file_ext}）は許可されていません。{allow_ext}のみ許可されています'])]
    case FILE_FORMAT_NOT_ALLOW = 28;

    #[EnumCode(msg: '绑定成功', i18nMsg: ['en' => 'Bind successful', 'zh_tw' => '綁定成功', 'zh_hk' => '綁定成功', 'ja' => 'バインドに成功しました'])]
    case BIND_SUCCESS = 29;

    #[EnumCode(msg: '绑定失败，请重试', i18nMsg: ['en' => 'Binding failed, please try again', 'zh_tw' => '綁定失敗，請重試', 'zh_hk' => '綁定失敗，請重試', 'ja' => 'バインドに失敗しました。再試行してください'])]
    case BIND_FAILED = 30;

    #[EnumCode(msg: '手机号格式不正确', i18nMsg: ['en' => 'Phone number format is incorrect', 'zh_tw' => '手機號格式不正確', 'zh_hk' => '手機號格式不正確', 'ja' => '電話番号の形式が正しくありません'])]
    case PHONE_FORMAT_ERROR = 31;

    #[EnumCode(msg: '请先删除子数据后，再删除此数据', i18nMsg: ['en' => 'Please delete the sub-data first, then delete this data', 'zh_tw' => '請先刪除子數據後，再刪除此數據', 'zh_hk' => '請先刪除子數據後，再刪除此數據', 'ja' => '子データを削除してからこのデータを削除してください'])]
    case HAS_SUB_DATA = 33;

    #[EnumCode(msg: '文件标识未传递，请重试', i18nMsg: ['en' => 'File identifier not passed, please try again', 'zh_tw' => '文件標識未傳遞，請重試', 'zh_hk' => '文件標識未傳遞，請重試', 'ja' => 'ファイル識別子が渡されていません。再試行してください'])]
    case IMPORT_FILE_ID_EMPTY = 34;

    #[EnumCode(msg: '文件已失效，请重新导入后下载', i18nMsg: ['en' => 'File has expired, please re-import and download', 'zh_tw' => '文件已失效，請重新導入後下載', 'zh_hk' => '文件已失效，請重新導入後下載', 'ja' => 'ファイルが有効期限切れです。再インポートしてダウンロードしてください'])]
    case IMPORT_FILE_EXPIRED = 35;

    #[EnumCode(msg: '字段（{field}）不能为空', i18nMsg: ['en' => 'Field ({field}) cannot be empty', 'zh_tw' => '欄位（{field}）不能為空', 'zh_hk' => '欄位（{field}）不能為空', 'ja' => 'フィールド（{field}）は空にできません'])]
    case PARAMS_EMPTY_WITH_FIELD = 36;

    #[EnumCode(msg: '字段（{field}）错误', i18nMsg: ['en' => 'Field ({field}) error', 'zh_tw' => '欄位（{field}）錯誤', 'zh_hk' => '欄位（{field}）錯誤', 'ja' => 'フィールド（{field}）のエラー'])]
    case PARAMS_WRONG_WITH_FIELD = 37;

    #[EnumCode(msg: '邮箱格式错误', i18nMsg: ['en' => 'Email format error', 'zh_tw' => '郵箱格式錯誤', 'ja' => 'メールフォーマットエラー', 'zh_hk' => '郵箱格式錯誤'])]
    case EMAIL_RULE_ERROR = 38;

    #[EnumCode(msg: '图片mime类型（{file_mime}）不允许，只允许（{allow_mime}）', i18nMsg: ['en' => 'Image mime type ({file_mime}) is not allowed, only allow ({allow_mime})', 'zh_tw' => '圖片mime類型（{file_mime}）不允许，只允許（{allow_mime}）', 'zh_hk' => '圖片mime類型（{file_mime}）不允许，只允許（{allow_mime}）', 'ja' => '画像のMIMEタイプ（{file_mime}）は許可されていません。{allow_mime}のみ許可されています'])]
    case FILE_MIME_NOT_ALLOW = 39;

    #[EnumCode(msg: '名称重复，请编辑后重试', i18nMsg: ['en' => 'The name is duplicated, please edit it and try again', 'zh_tw' => '名稱重複，請編輯後重試', 'ja' => '名前が重複しています。編集してもう一度お試しください。', 'zh_hk' => '名稱重複，請編輯後重試'])]
    case NAME_REPEAT = 40;

    #[EnumCode(msg: '该相关请求正在执行，请稍后再试', i18nMsg: ['en' => 'The related request is being executed, please try again later', 'zh_tw' => '該相關請求正在執行，請稍後再試', 'zh_hk' => '該相關請求正在執行，請稍後再試', 'ja' => '関連するリクエストが実行中です。しばらくしてから再試行してください。'])]
    case RATE_REQUEST_EXECUTING = 41;

    #[EnumCode(msg: '[技术错误]如果请求参数为空，则需要先引用鉴权登录的中间件生成协程中的用户信息', i18nMsg: ['en' => '[Technical error] If the request parameter is empty, you need to reference the authentication login middleware to generate the user information in the coroutine', 'zh_tw' => '[技術錯誤]如果請求參數為空，則需要先引用認證登錄的中間件生成協程中的用戶信息', 'zh_hk' => '[技術錯誤]如果請求參數為空，則需要先引用認證登錄的中間件生成協程中的用戶信息', 'ja' => '[技術エラー]リクエストパラメータが空の場合は、認証ログインミドルウェアを参照して、コルーチン内のユーザー情報を生成する必要があります'])]
    case RATE_REQUEST_PARAMS_EMPTY = 42;
}

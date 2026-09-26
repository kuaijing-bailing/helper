<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */

namespace Bailing\JsonRpc\Third;

interface ThirdServiceInterface
{
    /**
     * 通过指定条件获取第三方授权用户信息.
     */
    public function getThirdUser(array $where): array;

    /**
     * 生成微信公众号授权URL.
     */
    public function buildWechatAuthUrl(string $wechatAppid, string $callbackUrl): array;

    /**
     * 生成支付宝授权URL.
     */
    public function buildAlipayAuthUrl(string $alipayAppid, string $callbackUrl): array;

    /**
     * 发送微信模板消息给到用户.
     * @param int $orgId 机构ID
     * @param int $userId 用户服务ID
     * @param array $pushData 推送内容
     * @param string $url 链接
     */
    public function pushWechatToSingleByUid(int $orgId, int $userId, array $pushData, string $url): array;

    /**
     * 发送微信小程序账单订阅消息，机构及用户身份独立于服务号校验。
     * @param int $orgId 账单所属机构 ID
     * @param int $userId 与站内信一致的接收用户 ID
     * @param array $pushData 与服务号一致的平铺参数：matter、tmpl_id 和中文业务字段；third 内部生成去重键并转换微信字段
     * @param string $link 与站内信一致的原始业务别名链接，包含账单 ID 及机构 ID
     * @return array 仅微信确认发送成功时返回成功；data 包含投递状态和记录 ID，不含用户 openid
     */
    public function pushWxappToSingleByUid(int $orgId, int $userId, array $pushData, string $link): array;

    /**
     * 获取企业微信信息.
     */
    public function getWorkWechatByOrgId(int $orgId): array;

    /**
     * 获取企业微信联系二维码.
     */
    public function getWorkWechatContactQrcode(int $orgId, array $uidArr, int $scene, bool $isTemp = false): array;

    /**
     * 获取微信公众号带参数二维码.
     */
    public function getWechatMpQrcode(int $orgId, string $sceneStr, string $appid = '', bool $isTemp = true): array;

    /**
     * 获取微信小程序带参数二维码.
     * @param int $orgId 机构ID
     * @param string $path 页面路径，场景码也一样通过?组装参数，程序会分割
     * @param string $appid 限定使用的小程序
     * @param bool $isScene 是否生成场景二维码，永久有效，数量无限制，参数限制32个字符（只支持数字，大小写英文以及部分特殊字符：!#$&'()*+,/:;=?@-._~）
     */
    public function getWechatAppQrcode(int $orgId, string $path, string $appid = '', bool $isScene = false): array;

    /**
     * 获取openid.
     */
    public function getOpenidByUserid(int $orgId, int $userId): array;

    /**
     * 根据appid获取accessToken.
     */
    public function getWechatAccessToken(string $appid): array;

    public function getFeishuInfoByOrgId(int $orgId): array;
}

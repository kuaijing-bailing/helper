<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\JsonRpc\Message;

interface SmsServiceInterface
{
    /**
     * 发送短信.
     * @param string $phone 手机号
     * @param string $phoneCountry 手机区号
     * @param string $alias 模板别名
     * @param array $templateParam 模板参数
     */
    public function sendSms(string $phone, string $phoneCountry, string $alias, array $templateParam, int $orgId = 0, string $microName = ''): array;

    /**
     * 校验验证码.
     */
    public function checkSmsCode(string $phone, string $phoneCountry, string $code): array;

    /**
     * 发送短信.
     * @param string $email 邮箱地址
     * @param string $alias 模板别名
     * @param array $templateParam 模板参数
     */
    public function sendMail(string $email, string $alias, array $templateParam, int $orgId = 0, string $microName = ''): array;

    /**
     * 校验验证码.
     */
    public function checkMailCode(string $email, string $code): array;

    /**
     * 图形验证码校验.
     */
    public function checkVerifyCode(string $token, string $input): array;

    /**
     * 校验并消费滑块验证码已验证令牌
     * 一次性消费：校验通过后立即删除，防止同一令牌在有效期内被重放
     */
    public function checkCaptchaVerifiedToken(string $verifiedToken): array;

    /**
     * 解析当前请求实际生效的验证码类型，并判断是否因客户端版本发生降级
     *
     * @param string $curEnv 请求来源端口，如 wechatMiniApp、app、web
     * @param string $appVersion 客户端版本号，Web 端为空字符串
     * @return array 返回格式：['code' => int, 'data' => ['captcha_type' => int, 'need_version_compatible' => bool]]
     */
    public function resolveCaptchaType(string $curEnv, string $appVersion): array;
}

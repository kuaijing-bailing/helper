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
     * 图形验证码校验.
     */
    public function checkVerifyCode(string $token, string $input): array;
}

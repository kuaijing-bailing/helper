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

use Bailing\Helper\ApiHelper;
use Hyperf\RpcClient\AbstractServiceClient;

class SmsServiceConsumer extends AbstractServiceClient implements SmsServiceInterface
{
    /**
     * 定义对应服务提供者的服务名称.
     */
    protected string $serviceName = 'SmsService';

    /**
     * 定义对应服务提供者的服务协议.
     */
    protected string $protocol = 'jsonrpc-http';

    public function sendSms(string $phone, string $phoneCountry, string $alias, array $templateParam, int $orgId = 0, string $microName = ''): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('phone', 'phoneCountry', 'alias', 'templateParam', 'orgId', 'microName'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    public function checkSmsCode(string $phone, string $phoneCountry, string $code): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('phone', 'phoneCountry', 'code'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    public function sendMail(string $email, string $alias, array $templateParam, int $orgId = 0, string $microName = ''): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('email', 'alias', 'templateParam', 'orgId', 'microName'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    public function checkMailCode(string $email, string $code): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('email', 'code'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    public function checkVerifyCode(string $token, string $input): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('token', 'input'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 校验并消费滑块验证码已验证令牌
     *
     * @param string $verifiedToken 已验证令牌
     * @return array
     */
    public function checkCaptchaVerifiedToken(string $verifiedToken): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('verifiedToken'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 解析当前请求实际生效的验证码类型，并判断是否因客户端版本发生降级
     *
     * @param string $curEnv 请求来源端口
     * @param string $appVersion 客户端版本号
     * @return array
     */
    public function resolveCaptchaType(string $curEnv, string $appVersion): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('curEnv', 'appVersion'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }
}

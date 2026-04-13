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

use Bailing\Helper\ApiHelper;
use Hyperf\RpcClient\AbstractServiceClient;

class ThirdServiceConsumer extends AbstractServiceClient implements ThirdServiceInterface
{
    /**
     * 定义对应服务提供者的服务名称.
     */
    protected string $serviceName = 'ThirdService';

    /**
     * 定义对应服务提供者的服务协议.
     */
    protected string $protocol = 'jsonrpc-http';

    /**
     * 通过指定条件获取第三方授权用户信息.
     */
    public function getThirdUser(array $where): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('where'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 生成微信公众号授权URL.
     */
    public function buildWechatAuthUrl(string $wechatAppid, string $callbackUrl): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('wechatAppid', 'callbackUrl'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 生成支付宝授权URL.
     */
    public function buildAlipayAuthUrl(string $alipayAppid, string $callbackUrl): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('alipayAppid', 'callbackUrl'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 发送微信模板消息给到用户.
     * @param int $orgId 机构ID
     * @param int $userId 用户服务ID
     * @param array $pushData 推送内容
     * @param string $url 链接
     */
    public function pushWechatToSingleByUid(int $orgId, int $userId, array $pushData, string $url): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orgId', 'userId', 'pushData', 'url'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 获取企业微信信息.
     */
    public function getWorkWechatByOrgId(int $orgId): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orgId'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 获取企业微信联系二维码.
     */
    public function getWorkWechatContactQrcode(int $orgId, array $uidArr, int $scene, bool $isTemp = false): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orgId', 'uidArr', 'scene', 'isTemp'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 获取微信公众号带参数二维码.
     */
    public function getWechatMpQrcode(int $orgId, string $sceneStr, string $appid = '', bool $isTemp = true): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orgId', 'sceneStr', 'appid', 'isTemp'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 获取微信小程序带参数二维码.
     * @param int $orgId 机构ID
     * @param string $path 页面路径，场景码也一样通过?组装参数，程序会分割
     * @param string $appid 限定使用的小程序
     * @param bool $isScene 是否生成场景二维码，永久有效，数量无限制，参数限制32个字符（只支持数字，大小写英文以及部分特殊字符：!#$&'()*+,/:;=?@-._~）
     */
    public function getWechatAppQrcode(int $orgId, string $path, string $appid = '', bool $isScene = false): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orgId', 'path', 'appid', 'isScene'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 获取openid.
     */
    public function getOpenidByUserid(int $orgId, int $userId): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orgId', 'userId'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    public function getFeishuInfoByOrgId(int $orgId): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orgId'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }
}

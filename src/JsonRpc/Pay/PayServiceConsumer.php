<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\JsonRpc\Pay;

use Bailing\Helper\ApiHelper;
use Hyperf\RpcClient\AbstractServiceClient;

class PayServiceConsumer extends AbstractServiceClient implements PayServiceInterface
{
    /**
     * 定义对应服务提供者的服务名称.
     */
    protected string $serviceName = 'PayService';

    /**
     * 定义对应服务提供者的服务协议.
     */
    protected string $protocol = 'jsonrpc-http';

    /**
     * 下单.
     */
    public function addOrder(array $orderData): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orderData'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 获取订单.
     * @param string $business 业务类型
     * @param int $businessId 业务订单ID
     */
    public function getOrder(string $business, int $businessId): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('business', 'businessId'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 获取多个订单.
     * @param string $business 业务类型
     * @param array $businessIdArr 业务订单ID
     */
    public function getOrderMore(string $business, array $businessIdArr): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('business', 'businessIdArr'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 修改订单价格.
     * @param string $orderId 支付业务订单号
     * @param float $orderMoney 新的订单金额
     */
    public function changeOrderMoney(string $orderId, float $orderMoney): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orderId', 'orderMoney'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 订单退款.
     * @param string $orderId 支付业务订单号
     * @param float $refundMoney 退款金额
     * @param string $remark 备注
     */
    public function refundOrderMoney(string $orderId, float $refundMoney, string $remark, array $extra = []): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orderId', 'refundMoney', 'remark', 'extra'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 获取支付宝应用appid对应的密钥，一般只有third服务在支付授权时使用.
     * @param int $orgId 机构ID
     * @param string $appid 支付宝应用appid
     */
    public function getAlipayParams(int $orgId, string $appid): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orgId', 'appid'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 企业付款.
     *
     * @param int $orgId 机构ID
     * @param string $type 支付方式 enum('wxpay', 'alipay')
     * @param float $money 金额
     * @param array $orderData ['openid' => '用户openid(必填)', 'appid' => '可不填', 'remark' => '付款备注(必填)']
     */
    public function merchantPay(int $orgId, string $type, float $money, array $orderData): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('orgId', 'type', 'money', 'orderData'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }

    /**
     * 生成支付业务场景.
     *
     * @param string $business 业务类型
     * @param string $business_name 业务名称
     */
    public function insertBusiness(string $business, string $business_name): array
    {
        try {
            return $this->__request(__FUNCTION__, compact('business', 'business_name'));
        } catch (\Exception $exception) {
            return ApiHelper::genServiceErrorData($this->serviceName, $exception);
        }
    }
}

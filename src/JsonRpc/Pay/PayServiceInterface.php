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

interface PayServiceInterface
{
    /**
     * 下单.
     */
    public function addOrder(array $orderData): array;

    /**
     * 获取订单.
     * @param string $business 业务类型
     * @param int $businessId 业务订单ID
     */
    public function getOrder(string $business, int $businessId): array;

    /**
     * 获取多个订单.
     * @param string $business 业务类型
     * @param array $businessIdArr 业务订单ID
     */
    public function getOrderMore(string $business, array $businessIdArr): array;

    /**
     * 修改订单价格.
     * @param string $orderId 支付业务订单号
     * @param float $orderMoney 新的订单金额
     */
    public function changeOrderMoney(string $orderId, float $orderMoney): array;

    /**
     * 订单退款.
     * @param string $orderId 支付业务订单号
     * @param float $refundMoney 退款订单金额
     * @param string $remark 备注
     */
    public function refundOrderMoney(string $orderId, float $refundMoney, string $remark): array;

    /**
     * 获取支付宝应用appid对应的密钥，一般只有third服务在支付授权时使用.
     * @param int $orgId 机构ID
     * @param string $appid 支付宝应用appid
     */
    public function getAlipayParams(int $orgId, string $appid): array;

    /**
     * 企业付款.
     *
     * @param int $orgId 机构ID
     * @param string $type 支付方式 enum('wxpay', 'alipay')
     * @param float $money 金额
     * @param array $orderData ['order_id' => '订单ID(必填)', 'openid' => '用户openid(必填)', 'appid' => '可不填', 'remark' => '付款备注(必填)', 'notify_url' => '回调地址(必填)']
     */
    public function merchantPay(int $orgId, string $type, float $money, array $orderData): array;

    /**
     * 生成支付业务场景.
     *
     * @param string $business 业务类型
     * @param string $business_name 业务名称
     */
    public function insertBusiness(string $business, string $business_name): array;
}

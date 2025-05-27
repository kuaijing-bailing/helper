<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
use esign\comm\EsignHttpHelper;
use esign\comm\EsignLogHelper;
use esign\Config;
use esign\emun\HttpEmun;

header('Content-type:text/html;charset=utf-8');
include '../../../EsignOpenAPI.php';
$config = Config::$config;
/**
 * 套餐服务API.
 * @date  2022/09/02 9:51
 */
$scence = 1;   //场景编号
switch ($scence) {
    case '1':
        //获取购买e签宝套餐链接
        getBuyOrderUrl();
        break;
    case '2':
        //查询e签宝套餐余量
        queryRemainingQuantity();
        break;
    case '3':
        //查询套餐订单列表
        queryOrderList();
        break;
    case '4':
        //查询套餐订单列表（页面版）
        getOrderManageUrl();
        break;
    default:
        EsignLogHelper::printMsg('场景选择错误');
}

/**
 * 获取购买e签宝套餐链接
 * /v3/orders/org-place-order-url.
 */
function getBuyOrderUrl()
{
    global $config;
    EsignLogHelper::printMsg('**********获取购买e签宝套餐链接开始**********');
    $apiaddr = '/v3/orders/org-place-order-url';

    $data = [
        'orgId' => 'xxxx',
        'transactorPsnId' => 'xxxx',
        'redirectUrl' => 'https://www.esign.com',
        'notifyUrl' => 'http://www.test.com:8081/CSTNotify/asyn/notify',
        'customBizNum' => '11222',
    ];
    $requestType = HttpEmun::POST;
    $paramStr = json_encode($data);
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    if ($response->getStatus() == 200) {
        EsignLogHelper::printMsg('获取购买e签宝套餐链接调用成功: ' . $response->getBody());
    } else {
        EsignLogHelper::printMsg('获取购买e签宝套餐链接失败，HTTP错误码' . $response->getStatus());
    }
    EsignLogHelper::printMsg('**********获取购买e签宝套餐链接调用结束**********');
}

/**
 * 查询e签宝套餐余量
 * /v3/orders/remaining-quantity?orgId=xx.
 */
function queryRemainingQuantity()
{
    global $config;
    EsignLogHelper::printMsg('**********查询e签宝套餐余量开始**********');
    $orgId = 'xxxx';
    $apiaddr = '/v3/orders/remaining-quantity?orgId=%s&distributor=true';
    $apiaddr = sprintf($apiaddr, $orgId);

    $requestType = HttpEmun::GET;
    $paramStr = null;
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    if ($response->getStatus() == 200) {
        EsignLogHelper::printMsg('查询e签宝套餐余量调用成功: ' . $response->getBody());
    } else {
        EsignLogHelper::printMsg('查询e签宝套餐余量失败，HTTP错误码' . $response->getStatus());
    }
    EsignLogHelper::printMsg('**********查询e签宝套餐余量调用结束**********');
}

/**
 * 查询套餐订单列表
 * /v3/orders/order-list.
 */
function queryOrderList()
{
    global $config;
    EsignLogHelper::printMsg('**********查询套餐订单列表开始**********');
    $orgId = 'xxxx';
    $apiaddr = '/v3/orders/order-list?orgId=%s&distributor=true';
    $apiaddr = sprintf($apiaddr, $orgId);

    $requestType = HttpEmun::GET;
    $paramStr = null;
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    if ($response->getStatus() == 200) {
        EsignLogHelper::printMsg('查询套餐订单列表调用成功: ' . $response->getBody());
    } else {
        EsignLogHelper::printMsg('查询套餐订单列表失败，HTTP错误码' . $response->getStatus());
    }
    EsignLogHelper::printMsg('**********查询套餐订单列表调用结束**********');
}

/**
 * 查询套餐订单列表（页面版）
 * /v3/orders/org-order-manage-url.
 */
function getOrderManageUrl()
{
    global $config;
    EsignLogHelper::printMsg('**********查询套餐订单列表（页面版）开始**********');
    $orgId = 'xxxx';
    $apiaddr = '/v3/orders/org-order-manage-url';
    $apiaddr = sprintf($apiaddr, $orgId);

    $requestType = HttpEmun::POST;
    $data = [
        'transactorPsnId' => 'xxxx',
        'orgId' => "{$orgId}",
        'distributor' => true,
    ];
    $paramStr = json_encode($data);
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    if ($response->getStatus() == 200) {
        EsignLogHelper::printMsg('查询套餐订单列表（页面版）调用成功: ' . $response->getBody());
    } else {
        EsignLogHelper::printMsg('查询套餐订单列表（页面版）失败，HTTP错误码' . $response->getStatus());
    }
    EsignLogHelper::printMsg('**********查询套餐订单列表（页面版）调用结束**********');
}

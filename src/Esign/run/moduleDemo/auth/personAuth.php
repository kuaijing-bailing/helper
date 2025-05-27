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

header('Content-type:application/pdf;charset=utf-8');
include '../../../EsignOpenAPI.php';

/**
 * 认证和授权服务-个人API.
 * @date  2022/09/02 9:51
 */
$config = Config::$config;
//OF-2202f8c3ae080018

//场景：1代表认证；2代表授权
$scence = 1;   //场景编号
//个人账户
$psnAccount = 'xxxxx';
switch ($scence) {
    case '1':
        //个人实名
        $realnameStatus = personsIdentityInfo($psnAccount, $scence);
        if ($realnameStatus == 1) {
            echo '客户已实名，请继续后续业务逻辑';
        } else {
            echo '客户未实名，请实名';
            psnAuthUrl($psnAccount);
        }
        break;
    case '2':
        //个人授权
        //$psnId查询个人实名状态获取
        $psnId = 'xxx';
        $authorizeUserInfo = personsIdentityInfo($psnAccount, $scence);
        if ($authorizeUserInfo) {
            $response = personsAuthorizedInfo($psnId);
            $expireTime = json_decode($response->getBody())->data->authorizedInfo[0]->expireTime;
            //计算是否授权过期
            $num = expireTime($expireTime);
            if ($num > 0) {
                echo '授权有效';
            } else {
                echo '请重新授权';
                psnAuthorizehUrl($psnAccount);
            }
        } else {
            echo '请授权';
            psnAuthorizehUrl($psnAccount);
        }

        break;
    default:
        EsignLogHelper::printMsg('场景选择错误');
}

/**
 * @param $expireTime
 * @return float
 */
function expireTime($expireTime)
{
    [$t1, $t2] = explode(' ', microtime());
    $nowTime = (float) sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    return $expireTime - $nowTime;
}

/**
 * 发起个人实名.
 * @param $psnAccount
 */
function psnAuthUrl($psnAccount)
{
    global $config;
    $apiaddr = '/v3/psn-auth-url';
    $requestType = HttpEmun::POST;
    $data = [
        'psnAuthConfig' => [
            'psnAccount' => $psnAccount,
            'psnInfo' => [
                'psnName' => '',
            ],
        ],
        'redirectConfig' => [
            'redirectUrl' => 'https://www.baidu.com/',
        ],
        'notifyUrl' => '',
        'clientType' => 'ALL',
        'appScheme' => '',
    ];
    $paramStr = json_encode($data);
    //生成签名验签+json体的header

    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);
    //发起接口请求
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
}

/**
 * 发起个人授权.
 * @param $psnAccount
 */
function psnAuthorizehUrl($psnAccount)
{
    global $config;
    $apiaddr = '/v3/psn-auth-url';
    $requestType = HttpEmun::POST;
    $data = [
        'psnAuthConfig' => [
            'psnAccount' => $psnAccount,
            'psnInfo' => [
                'psnName' => '',
            ],
        ],
        'authorizeConfig' => [
            'authorizedScopes' => [
                'get_psn_identity_info', 'psn_initiate_sign', 'manage_psn_resource',
            ],
        ],
        'redirectConfig' => [
            'redirectUrl' => 'https://www.baidu.com/',
        ],
        'notifyUrl' => '',
        'clientType' => 'ALL',
        'appScheme' => '',
    ];
    $paramStr = json_encode($data);
    //生成签名验签+json体的header

    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);
    //发起接口请求
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
}

/**
 * 查询个人认证信息.
 * @param $psnId
 * @param $scence
 * @return mixed
 */
function personsIdentityInfo($psnId, $scence)
{
    global $config;
    $apiaddr = '/v3/persons/identity-info?psnAccount=' . $psnId;
    $requestType = HttpEmun::GET;
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], '', $requestType, $apiaddr);
    //发起接口请求
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, '');
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());

    $realnameStatus = json_decode($response->getBody())->data->realnameStatus;

    $authorizeUserInfo = json_decode($response->getBody())->data->authorizeUserInfo;

    if ($scence == 1) {
        return $realnameStatus;
    }
    return $authorizeUserInfo;
}

/**
 * 查询个人授权信息.
 * @param $psnId
 * @return \esign\comm\EsignResponse
 */
function personsAuthorizedInfo($psnId)
{
    global $config;
    $apiaddr = '/v3/persons/' . $psnId . '/authorized-info';
    $requestType = HttpEmun::GET;
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], '', $requestType, $apiaddr);
    //发起接口请求
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, '');
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    return $response;
}

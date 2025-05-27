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
 * 企业机构成员服务API.
 * @date  2022/09/02 9:51
 */
$config = Config::$config;
//必须要确保企业用户已授予资源管理权限（manage_org_resource）
$orgid = '75e55279XXXXXX70d8336882bd0'; //企业机构账号id
$psnId = '089e8036XXXXXXXe98afb6332477abb58  '; //个人账号id
$scence = 1;  //场景编号
switch ($scence) {
    case '1':
        //查询企业成员列表
        menbersListInfoQuery($orgid);
        break;
    case '2':
        //查询企业管理员
        administratorsQuery($orgid);
        break;
    case '3':
        //添加企业机构成员
        addmembers($orgid, $psnId);
        break;
    case '4':
        //移除企业机构成员
        deletemembers($orgid, $psnId);
        break;
    default:
        EsignLogHelper::printMsg('场景选择错误');
}
//查询企业成员列表
function menbersListInfoQuery($orgid)
{
    global $config;
    $apiaddr = '/v3/organizations/' . $orgid . '/member-list?pageNum=1&pageSize=100';
    $requestType = HttpEmun::GET;
    //生成签名验签+json体的header
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], '', $requestType, $apiaddr);
    //发起接口请求
    //发起接口请求
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, '');
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
}
//查询企业管理员
function administratorsQuery($orgid)
{
    global $config;
    $apiaddr = '/v3/organizations/' . $orgid . '/administrators';
    $requestType = HttpEmun::GET;
    //生成签名验签+json体的header
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], '', $requestType, $apiaddr);
    //发起接口请求
    //发起接口请求
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, '');
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
}
//添加企业机构成员
function addmembers($orgid, $psnId)
{
    global $config;
    $apiaddr = '/v3/organizations/' . $orgid . '/members';
    $requestType = HttpEmun::POST;
    //生成签名验签+json体的header
    $data = [
        'members' => [
            [
                'psnId' => $psnId,
                'memberName' => '测试人员1号', //员工昵称，用于区分企业成员
                'employeeNum' => '005', //员工编号（用户自定义，可以为企业工号）
            ],
        ],
    ];
    $paramStr = json_encode($data);
    // EsignLogHelper::printMsg($paramStr);
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);
    //发起接口请求
    //发起接口请求
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
}
//移除企业机构成员
function deletemembers($orgid, $psnId)
{
    global $config;
    $apiaddr = '/v3/organizations/' . $orgid . '/members';
    $requestType = HttpEmun::DELETE;
    //生成签名验签+json体的header
    $data = [
        'memberPsnIds' => [
            $psnId,
        ],
    ];
    $paramStr = json_encode($data);
    echo $paramStr;
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);
    //发起接口请求
    //发起接口请求
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
}

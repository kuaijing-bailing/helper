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

ini_set('date.timezone', 'Asia/Shanghai');
header('Content-type:application/pdf;charset=utf-8');
include '../../../EsignOpenAPI.php';

/**
 * 印章服务-企业API.
 * @date  2022/09/02 9:51
 */
$config = Config::$config;
$orgId = '76c349c888124XXXXXXd91fa631986'; //企业机构账号id
$scence = '1';   //场景编号
switch ($scence) {
    case '1':
        //创建机构模板印章
        createOrgsealsbyTemplate($orgId);
        //查询企业内部印章
        internalauthInfo($orgId);
          break;
    case '2':
        $sealId = '8e307bf9-0XXXXXXXab-b86568a62dda'; //授权印章id;
        //内部成员授权
        orgsealsInternalAuth($orgId, $sealId);
        //查询对内部成员授权详情
        orgsealsInternalAuth($orgId, $sealId);
        break;
    case '3':
        $sealId = '8e307bf9-0*******9-83ab-b86568a62dda';
        $transactorPsnId = '7ffcaed8c1b******ca1d8f0ef0a8f6'; //授权操作人账号ID（委托机构的法定代表人或企业管理员个人账号ID）
        //跨企业授权
        orgsealsExternalAuth($orgId, $sealId, $transactorPsnId);
        //查询被外部企业授权印章
        orgauthorizedSealList($orgId);

        break;
    case '4':
        //授权业务流程编号
        $sealAuthBizId = '80710c23-c5XXXXXXX87-aed1596b7b03';
        //解除印章授权
        orgsealsAuthDelete($orgId, $sealAuthBizId);
        break;
    default:
        EsignLogHelper::printMsg('场景选择错误');
}
//创建机构模板印章接口
function createOrgsealsbyTemplate($orgId)
{
    global $config;
    $apiaddr = '/v3/seals/org-seals/create-by-template';
    $requestType = HttpEmun::POST;
    //生成签名验签+json体的header
    $data = [
        'orgId' => $orgId,
        'sealName' => '合同印章', //机构印章名称（用户自定义，且名称不可重复）
        'sealTemplateStyle' => 'CONTRACT_ROUND_NO_STAR', //机构模板印章样式
        'sealSize' => '38_38',
        'sealColor' => 'RED', //机构印章颜色，默认是红色
        'sealHorizontalText' => '合同专用章', //自定义印章横向文，示例值：XX专用章
        'sealBottomText' => '9111113331', //印章下弦文（实体印章防伪码），示例值：9133********1
    ];
    $paramStr = json_encode($data);
    //请求参数
    // EsignLogHelper::printMsg($paramStr);
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);
    //发起接口请求
    //发起接口请求头
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    $code = json_decode($response->getBody())->code;
    if ($code === 0) {
        EsignLogHelper::printMsg('机构模板印章接口调用成功');
        EsignLogHelper::printMsg($response->getBody());
    } else {
        EsignLogHelper::printMsg($response->getBody());
    }
}

//查询企业内部印章接口
function internalauthInfo($orgId)
{
    global $config;
    $apiaddr = '/v3/seals/org-own-seal-list?orgId=' . $orgId . '&pageNum=1&pageSize=20';
    $requestType = HttpEmun::GET;
    //生成签名验签+json体的header
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], '', $requestType, $apiaddr);
    //发起接口请求
    //发起接口请求
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, '');
    EsignLogHelper::printMsg($response->getStatus());
    $code = json_decode($response->getBody())->code;
    if ($code === 0) {
        EsignLogHelper::printMsg('查询企业内部印章接口调用成功');
        EsignLogHelper::printMsg($response->getBody());
    } else {
        EsignLogHelper::printMsg($response->getBody());
    }
}

//内部成员授权接口
function orgsealsInternalAuth($orgId, $sealId)
{
    global $config;
    $apiaddr = '/v3/seals/org-seals/internal-auth';
    $requestType = HttpEmun::POST;
    //生成签名验签+json体的header
    $data = [
        'orgId' => $orgId,
        'sealId' => $sealId, //机构印章名称（用户自定义，且名称不可重复）
        'authorizedPsnIds' => [
            '7ffcaed8c1b146c3aaca1d8f0ef0a8f6', //指定被授权成员（账号ID或ALL），传ALL时，sealRole印章角色只能指定印章使用员
        ],
        'sealRole' => 'SEAL_USER', //指定印章角色：SEAL_USER - 印章使用员（印章使用权限），SEAL_EXAMINER - 印章审批员（印章使用权限+用印审批权限）
        'transactorPsnId' => '626629f483b9445ab71224299f1d8c1d', //授权操作人账号id
        'sealAuthScope' => [
            'templateIds' => [
                'ALL',
            ], //授权范围列表
        ],
        'effectiveTime' => time() * 1000, //授权生效时间（Unix 时间戳格式，单位：毫秒）,示例中获取的当前时间为授权日期
        'expireTime' => strtotime('+365 day', time()) * 1000, //授权失效时间（Unix时间戳格式，单位：毫秒）示例中获取授权有效期为1年
        'redirectUrl' => 'https://open.esign.cn',
    ];
    $paramStr = json_encode($data);
    EsignLogHelper::printMsg($paramStr);
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);
    //发起接口请求
    //发起接口请求
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    $code = json_decode($response->getBody())->code;
    if ($code === 0) {
        EsignLogHelper::printMsg('内部成员授权接口调用成功');
        EsignLogHelper::printMsg($response->getBody());
    } else {
        EsignLogHelper::printMsg($response->getBody());
    }
}

//查询对内部成员授权详情接口
function internalauthQuery($orgId)
{
    global $config;
    $apiaddr = '/v3/seals/org-seals/internal-auth?authorizedPsnId=' . '7ffcaed8c1b146c3aaca1d8f0ef0a8f6' . '&orgId=' . $orgId . '&pageNum=1&pageSize=20';
    $requestType = HttpEmun::GET;
    //生成签名验签+json体的header
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], '', $requestType, $apiaddr);
    //发起接口请求
    //发起接口请求
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, '');
    EsignLogHelper::printMsg($response->getStatus());
    $code = json_decode($response->getBody())->code;
    if ($code === 0) {
        EsignLogHelper::printMsg('查询对内部成员授权详情接口调用成功');
        EsignLogHelper::printMsg($response->getBody());
    } else {
        EsignLogHelper::printMsg($response->getBody());
    }
}

//解除印章授权
function orgsealsAuthDelete($orgId, $sealAuthBizId)
{
    global $config;
    $apiaddr = '/v3/seals/org-seals/auth-delete';
    $requestType = HttpEmun::POST;
    //生成签名验签+json体的header
    $data = [
        'orgId' => $orgId,
        'deleteType' => 'sealAuthBizIds',
        'sealAuthBizIds' => [
            $sealAuthBizId,
        ],
    ];
    $paramStr = json_encode($data);
    //请求参数
    // EsignLogHelper::printMsg($paramStr);
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);
    //发起接口请求
    //发起接口请求
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    $code = json_decode($response->getBody())->code;
    if ($code === 0) {
        EsignLogHelper::printMsg('解除印章授权接口调用成功');
        EsignLogHelper::printMsg($response->getBody());
    } else {
        EsignLogHelper::printMsg($response->getBody());
    }
}

//跨企业授权接口
function orgsealsExternalAuth($orgId, $sealId, $transactorPsnId)
{
    global $config;
    $apiaddr = '/v3/seals/org-seals/external-auth';
    $requestType = HttpEmun::POST;

    //生成签名验签+json体的header
    $data = [
        'orgId' => $orgId,
        'sealId' => $sealId, //机构印章名称（用户自定义，且名称不可重复）
        'transactorPsnId' => $transactorPsnId,
        'authorizedOrgInfo' => [
            'orgName' => 'esigntest******章被授权方',
            'orgIDCardNum' => '911000*****000198',
        ],
        'effectiveTime' => time() * 1000, //授权生效时间（Unix 时间戳格式，单位：毫秒）,示例中获取的当前时间为授权日期
        'expireTime' => strtotime('+365 day', time()) * 1000, //授权失效时间（Unix时间戳格式，单位：毫秒）示例中获取授权有效期为1年
        'redirectUrl' => 'https://open.esign.cn',
    ];
    $paramStr = json_encode($data);
    //请求参数
    EsignLogHelper::printMsg($paramStr);
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);
    //发起接口请求
    //发起接口请求头
    //EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    $code = json_decode($response->getBody())->code;
    if ($code === 0) {
        EsignLogHelper::printMsg('跨企业授权接口调用成功');
        EsignLogHelper::printMsg($response->getBody());
    } else {
        EsignLogHelper::printMsg($response->getBody());
    }
}

//查询对外部企业授权详情接口
function orgauthorizedSealList($orgId)
{
    global $config;
    $apiaddr = '/v3/seals/org-authorized-seal-list?orgId=' . $orgId . '&pageNum=1&pageSize=20';
    $requestType = HttpEmun::GET;
    //生成签名验签+json体的header
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], '', $requestType, $apiaddr);
    //发起接口请求
    //发起接口请求头
    //EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, '');
    EsignLogHelper::printMsg($response->getStatus());
    $code = json_decode($response->getBody())->code;
    if ($code == 0) {
        EsignLogHelper::printMsg('查询被外部企业授权印章接口调用成功');
        EsignLogHelper::printMsg($response->getBody());
    } else {
        EsignLogHelper::printMsg($response->getBody());
    }
}

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
 * 印章服务-个人API.
 * @date  2022/09/02 9:51
 */
$config = Config::$config;
//1.代表创建个人印章-API；2.创建个人印章（页面版）；3.查询印章 ；4.变更个人印章
$psnId = '2af1914956XXXXXXXX559397a3dca73'; //个人账号ID
$scence = '1';  //场景编号
switch ($scence) {
    case '1':
        //创建个人模板印章
        createPsnsealsBytemplate($psnId);
        //查询个人印章列表
        psnsealListInfo($psnId);
        break;
    case '2':
        //获取创建个人印章页面链接
        psnsealCreateUrl($psnId);
        //查询个人印章列表
        psnsealListInfo($psnId);
        break;
    case '3':
        $sealmagePath = 'D://sealma.png'; //印章图片路径
        //创建个人图片印章
        createByImage($psnId, $sealmagePath);
        break;
    case '4':
        //获取管理个人印章页面链接
        psnsealsManageUrl($psnId);
        break;
    case '5':
      $sealId = '8e307bf9-0XXXXXXX-83ab-b86568a62dda'; ////个人印章Id
      //删除个人印章
      psnsealDelete($psnId, $sealId);
        break;
    default:
        EsignLogHelper::printMsg('场景选择错误');
}

//创建个人模板印章
function createPsnsealsBytemplate($psnId)
{
    global $config;
    $apiaddr = '/v3/seals/psn-seals/create-by-template';
    $requestType = HttpEmun::POST;
    //生成签名验签+json体的header
    $data = [
        'psnId' => $psnId,
        'sealName' => '个人测试印章',
        'sealTemplateStyle' => 'RECTANGLE_NO_BORDER',
        'sealSize' => '20_10',
        'sealColor' => 'RED', //个人印章颜色（默认值为RED）
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
        EsignLogHelper::printMsg('创建个人模板印章接口调用成功');
        EsignLogHelper::printMsg($response->getBody());
    } else {
        EsignLogHelper::printMsg($response->getBody());
    }
}

//查询个人印章列表
function psnsealListInfo($psnId)
{
    global $config;
    $apiaddr = '/v3/seals/psn-seal-list?psnId=' . $psnId . '&pageNum=1&pageSize=20';
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
        EsignLogHelper::printMsg('查询个人印章列表接口调用成功');
        EsignLogHelper::printMsg($response->getBody());
    } else {
        EsignLogHelper::printMsg($response->getBody());
    }
}

//删除个人印章接口
function psnsealDelete($psnId, $sealId)
{
    global $config;
    $apiaddr = '/v3/seals/psn-seal?psnId=' . $psnId . '&sealId=' . $sealId;
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
        EsignLogHelper::printMsg('查询个人印章列表接口调用成功');
        EsignLogHelper::printMsg($response->getBody());
    } else {
        EsignLogHelper::printMsg($response->getBody());
    }
}

//获取管理个人印章页面链接接口
function psnsealsManageUrl($psnId)
{
    global $config;
    $apiaddr = '/v3/seals/psn-seals-manage-url';
    $requestType = HttpEmun::POST;
    //生成签名验签+json体的header
    $data = [
        'psnId' => $psnId,
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
        EsignLogHelper::printMsg('获取管理个人印章页面链接接口调用成功');
        EsignLogHelper::printMsg($response->getBody());
    } else {
        EsignLogHelper::printMsg($response->getBody());
    }
}

//获取创建个人印章页面链接接口
function psnsealCreateUrl($psnId)
{
    global $config;
    $apiaddr = '/v3/seals/psn-seal-create-url';
    $requestType = HttpEmun::POST;
    //生成签名验签+json体的header
    $data = [
        'psnId' => $psnId,
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
        EsignLogHelper::printMsg('获取创建个人印章页面链接接口调用成功');
        EsignLogHelper::printMsg($response->getBody());
    } else {
        EsignLogHelper::printMsg($response->getBody());
    }
}

//上传印章图片接口  步骤一：获取印章图片上传地址fileUploadUrl
function getFilekey($contentBase64Md5, $fileSize, $sealmagePath)
{
    global $config;
    $apiaddr = '/v3/files/file-key';
    $requestType = HttpEmun::POST;
    //生成签名验签+json体的header
    $data = [
        'contentMd5' => $contentBase64Md5,
        'contentType' => 'application/octet-stream',
        'fileName' => '测试个人印章.png',
        'fileSize' => $fileSize,
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
    $fileUploadUrl = json_decode($response->getBody())->data->fileUploadUrl;
    $fileKey = json_decode($response->getBody())->data->fileKey;
    //步骤二：将印章图片文件流上传到fileUploadUrl
    EsignHttpHelper::upLoadFileHttp($fileUploadUrl, $sealmagePath, 'application/octet-stream');
    if ($code === 0) {
        EsignLogHelper::printMsg('上传印章图片接口调用成功');
        EsignLogHelper::printMsg($response->getBody());
        return $fileKey;
    }
    EsignLogHelper::printMsg($response->getBody());
}

//获取图片MD5
function getContentBase64Md5($sealmagePath)
{
    //  获取文件MD5的128位二进制数组
    $md5 = md5_file($sealmagePath, true);
    //  计算文件的Content-MD5
    return base64_encode($md5);
}

//创建个人图片印章接口
function createByImage($psnId, $sealmagePath)
{
    $contentBase64Md5 = getContentBase64Md5($sealmagePath);    //获取印章图片MD5值
    $fileSize = filesize($sealmagePath); //获取印章图片大小
    //上传印章图片接口步骤一：获取印章图片上传地址fileUploadUrl
    $fileKey = getFilekey($contentBase64Md5, $fileSize, $sealmagePath);

    global $config;
    $apiaddr = '/v3/seals/psn-seals/create-by-image';
    $requestType = HttpEmun::POST;
    //生成签名验签+json体的header
    $data = [
        'psnId' => $psnId,
        'sealImageFileKey' => $fileKey,
        'sealName' => '测试个人印章.png',
        'sealWidth' => '20',
        'sealHeight' => '10',
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
    $fileUploadUrl = json_decode($response->getBody())->data->fileUploadUrl;
    if ($code === 0) {
        EsignLogHelper::printMsg('上传印章图片接口调用成功');
        EsignLogHelper::printMsg($response->getBody());
        return $fileUploadUrl;
    }
    EsignLogHelper::printMsg($response->getBody());
}

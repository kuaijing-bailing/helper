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
use esign\comm\EsignUtilHelper;
use esign\Config;
use esign\emun\HttpEmun;

/**
 * 文件服务API.
 * @date  2022/09/02 9:51
 */
$config = Config::$config;

function contentMd5($filePath)
{
    if (! file_exists($filePath)) {
        EsignLogHelper::printMsg($filePath . '文件不存在');
        exit;
    }
    return EsignUtilHelper::getContentBase64Md5($filePath);
}

/**
 * @param $filePath
 * @param $contentMd5
 * @return mixed
 */
function fileUploadUrl($filePath, $contentMd5)
{
    global $config;
    $apiaddr = '/v3/files/file-upload-url';
    $requestType = HttpEmun::POST;
    $data = [
        'contentMd5' => $contentMd5,
        'contentType' => 'application/pdf',
        'convertToPDF' => false,
        'fileName' => '房屋租赁协议.pdf',
        'fileSize' => filesize($filePath),
    ];
    $paramStr = json_encode($data);
    //生成签名验签+json体的header

    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);
    //获取文件上传地址
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    $fileUploadUrl = json_decode($response->getBody())->data->fileUploadUrl;
    $fileId = json_decode($response->getBody())->data->fileId;
    //文件流put上传
    $response = EsignHttpHelper::upLoadFileHttp($fileUploadUrl, $filePath, 'application/pdf');
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    return $fileId;
}

/**
 * @param $filePath
 * @param $contentMd5
 * @return mixed
 */
function fileUploadToHtmlUrl($filePath, $contentMd5)
{
    global $config;
    $apiaddr = '/v3/files/file-upload-url';
    $requestType = HttpEmun::POST;
    $data = [
        'contentMd5' => $contentMd5,
        'contentType' => 'application/octet-stream',
        'fileName' => '房屋租赁协议.docx',
        'fileSize' => filesize($filePath),
        'convertToHTML' => true,
    ];
    $paramStr = json_encode($data);
    //生成签名验签+json体的header

    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);
    //获取文件上传地址
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    $fileUploadUrl = json_decode($response->getBody())->data->fileUploadUrl;
    $fileId = json_decode($response->getBody())->data->fileId;
    //文件流put上传
    $response = EsignHttpHelper::upLoadFileHttp($fileUploadUrl, $filePath, 'application/octet-stream');
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    return $fileId;
}

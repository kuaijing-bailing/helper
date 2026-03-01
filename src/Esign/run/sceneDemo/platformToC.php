<?php

/**
 * 本文件适用于平台方自动+个人用户手动签署场景
 * 基本流程如下：
 * 
 * 1、发起签署时，设置平台方自动签署区+个人签署区
 * 2、获取个人签署链接
 * 3、流程完结后，下载签署后文件  
 * 
 * @author  婉兮
 * @date  2022/09/02 9:51
 *
 */
header("Content-type:text/html;charset=utf-8");
include("../../EsignOpenAPI.php");
include("../moduleDemo/fileAndTemplate/file.php");

use esign\comm\EsignHttpHelper;
use esign\emun\HttpEmun;
use esign\Config;
use esign\comm\EsignLogHelper;

$mobile = "16657XXXX";
$config = Config::$config;

$flowId = createByFile();
$signUrl = getSignUrl($flowId);

//在接收到流程结束的回调通知后，调用签署文件下载接口
$isComplete = false;
if($isComplete){
    downloadFile($flowId);
}

function createByFile(){
    EsignLogHelper::printMsg("**********基于文件发起签署调用开始**********");
    global $config ,$mobile;
    $apiaddr="/v3/sign-flow/create-by-file";
    $requestType = HttpEmun::POST;
    //上传文件，获取文件id
    $filePath="/Users/cmn/Sites/V3 Demo/SaaSAPI_V3_Demo_PHP/pdf/test1.pdf";
    $contentMd5=contentMd5($filePath);
    $fileId=fileUploadUrl($filePath,$contentMd5);
    
    $data = [
        "docs" =>[
            [
                "fileId" =>$fileId,
                "fileName" =>"租赁合同.pdf"
            ]
        ],
        "signFlowConfig" => [
            "signFlowTitle" =>"房屋租赁协议测试",
            "autoFinish"=>true
        ],
        "signers" =>[
            [
                "psnSignerInfo" => [
                    "psnAccount" => $mobile
                ],
                "signFields" =>[
                    [
                        "fileId" =>$fileId,
                        "normalSignFieldConfig" => [
                            "autoSign" =>false,
                            "signFieldStyle" =>1,
                            "signFieldPosition" => [
                                "positionPage" =>"1",
                                "positionX" =>100,
                                "positionY" =>200
                            ]
                        ]
                    ]
                ],
                "signerType" =>0
            ],
            [
                "orgSignerInfo" => [
                    "orgId" => "2accac5189084d8d8e8bce74e79eXXXX"    //平台方机构oid
                ],
                "signFields" =>[
                    [
                        "fileId" =>$fileId,
                        "normalSignFieldConfig" => [
                            "autoSign" =>true,
                            "signFieldStyle" =>1,
                            "signFieldPosition" => [
                                "positionPage" =>"1",
                                "positionX" =>300,
                                "positionY" =>200
                            ]
                        ]
                    ]
                ],
                "signerType" =>1
            ]
        ]
    ];
    $paramStr = json_encode($data);

    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());

    $flowId = false;
    if($response->getStatus() == 200){
        $result = json_decode($response->getBody());
        if($result->code==0){
            $flowId = $result->data->signFlowId;
            EsignLogHelper::printMsg("基于文件发起签署接口调用成功，flowId: ".$flowId);
        }else{
            EsignLogHelper::printMsg("基于文件发起签署接口调用失败，错误信息: ".$result->message);
        }
    }else{
        EsignLogHelper::printMsg("基于文件发起签署接口调用失败，HTTP错误码".$response->getStatus());
    }
    EsignLogHelper::printMsg("**********基于文件发起签署调用结束**********");

    return $flowId;
}


function getSignUrl($flowId){
    EsignLogHelper::printMsg("**********获取合同文件签署链接开始**********");
    global $config ,$mobile;

    $apiaddr="/v3/sign-flow/%s/sign-url";
    $apiaddr = sprintf($apiaddr,$flowId);
    $requestType = HttpEmun::POST;
    $data = [
        "clientType" =>"ALL",
        "needLogin" =>false,
        "operator" => [
            "psnAccount" =>$mobile
        ],
        "urlType" =>2
    ];
    $paramStr = json_encode($data);

    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    $url= null;
    if($response->getStatus() == 200){
        $url =  json_decode($response->getBody())->data->shortUrl;
        EsignLogHelper::printMsg("获取合同文件签署链接调用成功，url: ".$url);
    }else{
        EsignLogHelper::printMsg("获取合同文件签署链接接口调用失败，HTTP错误码".$response->getStatus());
    }
    EsignLogHelper::printMsg("**********获取合同文件签署链接调用结束**********");
    return $url;
}

/**
 * 下载已签署文件及附属材料
 */
function downloadFile($flowId){
    global $config ;

    EsignLogHelper::printMsg("**********下载已签署文件及附属材料开始**********");
    $apiaddr="/v3/sign-flow/%s/file-download-url";
    $apiaddr = sprintf($apiaddr,$flowId);
    $requestType = HttpEmun::GET;
    $paramStr = null;
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    if($response->getStatus() == 200){
        EsignLogHelper::printMsg("下载已签署文件及附属材料调用成功: ".$response->getBody());
    }else{
        EsignLogHelper::printMsg("下载已签署文件及附属材料调用失败，HTTP错误码".$response->getStatus());
    }
    EsignLogHelper::printMsg("**********下载已签署文件及附属材料调用结束**********");
}



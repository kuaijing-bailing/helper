<?php

/**    
 *
 * 本文件适用于平台方自动+企业用户自动签署场景      
 * 基本流程如下：
 * 1、企业用户将印章授权给平台方，可通过登录e签宝官网企业控制台完成，或者通过印章开放服务完成：https://open.esign.cn/doc/opendoc/seal3/qkxyha
 * 2、发起签署时，设置signFields入参规则:signers.orgSignerInfo对象中的orgId为平台方企业账号id，assignedSealId为授权企业的印章id，autoSign设置为true
 * 3、流程完结，在接收到流程结束的回调通知后，下载签署后文件
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

$config = Config::$config;

$flowId= createByFile();
sleep(3);         //等待异步执行完成，接收到流程结束的回调通知后进行下载

//在接收到流程结束的回调通知后，调用签署文件下载接口
$isComplete = false;
if($isComplete){
    downloadFile($flowId);
}

function createByFile()
{
    EsignLogHelper::printMsg("**********基于文件发起签署调用开始**********");
    global $config ;
    $apiaddr="/v3/sign-flow/create-by-file";
    $requestType = HttpEmun::POST;
//上传文件，获取文件id
    $filePath="/Users/cmn/Sites/V3 Demo/SaaSAPI_V3_Demo_PHP/pdf/test.pdf";
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
                "orgSignerInfo" => [
                    "orgId" => "xxxxxx"     //其他企业自动签署，设置orgId平台方id
                ],
                "signFields" =>[
                    [
                        "fileId" =>$fileId,
                        "normalSignFieldConfig" => [
                            "autoSign" =>true,
                            "signFieldStyle" =>1,
                            "assignedSealId"=>"xxxxxxx",    // 在signFields中设置 assignedSealId为授权企业印章id
                            "signFieldPosition" => [
                                "positionPage" =>"1",
                                "positionX" =>100,
                                "positionY" =>200
                            ]
                        ]
                    ]
                ],
                "signerType" =>1
            ],
            [
                "orgSignerInfo" => [
                    "orgId" => "xxxxxx"    //平台方自身自动签署
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


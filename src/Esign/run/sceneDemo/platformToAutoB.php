<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
header('Content-type:text/html;charset=utf-8');
include '../../EsignOpenAPI.php';
include '../moduleDemo/fileAndTemplate/file.php';

use esign\comm\EsignHttpHelper;
use esign\comm\EsignLogHelper;
use esign\Config;
use esign\emun\HttpEmun;

$config = Config::$config;

$flowId = createByFile();
sleep(3);         //等待异步执行完成，接收到流程结束的回调通知后进行下载

//在接收到流程结束的回调通知后，调用签署文件下载接口
$isComplete = false;
if ($isComplete) {
    downloadFile($flowId);
}

function createByFile()
{
    EsignLogHelper::printMsg('**********基于文件发起签署调用开始**********');
    global $config;
    $apiaddr = '/v3/sign-flow/create-by-file';
    $requestType = HttpEmun::POST;
    //上传文件，获取文件id
    $filePath = '/Users/cmn/Sites/V3 Demo/SaaSAPI_V3_Demo_PHP/pdf/test.pdf';
    $contentMd5 = contentMd5($filePath);
    $fileId = fileUploadUrl($filePath, $contentMd5);
    $data = [
        'docs' => [
            [
                'fileId' => $fileId,
                'fileName' => '租赁合同.pdf',
            ],
        ],
        'signFlowConfig' => [
            'signFlowTitle' => '房屋租赁协议测试',
            'autoFinish' => true,
        ],
        'signers' => [
            [
                'orgSignerInfo' => [
                    'orgId' => 'xxxxxx',     //其他企业自动签署，设置orgId平台方id
                ],
                'signFields' => [
                    [
                        'fileId' => $fileId,
                        'normalSignFieldConfig' => [
                            'autoSign' => true,
                            'signFieldStyle' => 1,
                            'assignedSealId' => 'xxxxxxx',    // 在signFields中设置 assignedSealId为授权企业印章id
                            'signFieldPosition' => [
                                'positionPage' => '1',
                                'positionX' => 100,
                                'positionY' => 200,
                            ],
                        ],
                    ],
                ],
                'signerType' => 1,
            ],
            [
                'orgSignerInfo' => [
                    'orgId' => 'xxxxxx',    //平台方自身自动签署
                ],
                'signFields' => [
                    [
                        'fileId' => $fileId,
                        'normalSignFieldConfig' => [
                            'autoSign' => true,
                            'signFieldStyle' => 1,
                            'signFieldPosition' => [
                                'positionPage' => '1',
                                'positionX' => 300,
                                'positionY' => 200,
                            ],
                        ],
                    ],
                ],
                'signerType' => 1,
            ],
        ],
    ];
    $paramStr = json_encode($data);

    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());

    $flowId = false;
    if ($response->getStatus() == 200) {
        $result = json_decode($response->getBody());
        if ($result->code == 0) {
            $flowId = $result->data->signFlowId;
            EsignLogHelper::printMsg('基于文件发起签署接口调用成功，flowId: ' . $flowId);
        } else {
            EsignLogHelper::printMsg('基于文件发起签署接口调用失败，错误信息: ' . $result->message);
        }
    } else {
        EsignLogHelper::printMsg('基于文件发起签署接口调用失败，HTTP错误码' . $response->getStatus());
    }
    EsignLogHelper::printMsg('**********基于文件发起签署调用结束**********');
    return $flowId;
}

/**
 * 下载已签署文件及附属材料.
 * @param mixed $flowId
 */
function downloadFile($flowId)
{
    global $config;

    EsignLogHelper::printMsg('**********下载已签署文件及附属材料开始**********');
    $apiaddr = '/v3/sign-flow/%s/file-download-url';
    $apiaddr = sprintf($apiaddr, $flowId);
    $requestType = HttpEmun::GET;
    $paramStr = null;
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    if ($response->getStatus() == 200) {
        EsignLogHelper::printMsg('下载已签署文件及附属材料调用成功: ' . $response->getBody());
    } else {
        EsignLogHelper::printMsg('下载已签署文件及附属材料调用失败，HTTP错误码' . $response->getStatus());
    }
    EsignLogHelper::printMsg('**********下载已签署文件及附属材料调用结束**********');
}

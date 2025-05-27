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
include '../fileAndTemplate/file.php';
/**
 * 合同签署服务API.
 * @date  2022/09/02 9:51
 */
$config = Config::$config;

$scence = 7;      //场景编号
switch ($scence) {
    case '1':
        //基于文件发起签署
        createByFile();
        break;
    case '2':
        //获取合同文件签署链接
        getSignUrl();
        break;
    case '3':
        //下载已签署文件及附属材料
        downloadFile();
        break;
    case '4':
        //撤销签署流程
        revokeFlow();
        break;
    case '5':
        //催签流程中签署人
        urgeFlow();
        break;
    case '6':
        //查询签署流程详情
        queryFlowDetail();
        break;
    case '7':
        //查询签署流程列表
        queryFlowList();
        break;
    case '8':
        //追加签署区
        addSignFields();
        break;
    case '9':
        //删除签署区
        delSignFields();
        break;
    case '10':
        //核验合同文件签名有效性
        verifySignature();
        break;
    case '11':
        //获取区块链存证信息
        queryAntchainFileInfo();
        break;
    case '12':
        //核验区块链存证文件
        verifyAntchainFileInfo();
        break;
    case '13':
        //获取批量签页面链接（多流程）
        getBatchSignUrl();
        break;
    case '14':
        //获取合同解约链接
        rescissionFlow();
        break;
    default:
        EsignLogHelper::printMsg('场景选择错误');
}

/**
 * 基于文件发起签署.
 */
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
                'psnSignerInfo' => [
                    'psnAccount' => '1665714xxxx',
                ],
                'signFields' => [
                    [
                        'fileId' => $fileId,
                        'normalSignFieldConfig' => [
                            'autoSign' => false,
                            'signFieldStyle' => 1,
                            'signFieldPosition' => [
                                'positionPage' => '1',
                                'positionX' => 100,
                                'positionY' => 200,
                            ],
                        ],
                    ],
                ],
                'signerType' => 0,
            ],
            [
                'orgSignerInfo' => [
                    'orgId' => 'xxxxxxxx',
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
 * 获取合同文件签署链接
 * /v3/sign-flow/{signFlowId}/sign-url.
 */
function getSignUrl()
{
    $flowId = 'xxxx';
    EsignLogHelper::printMsg('**********获取合同文件签署链接开始**********');
    global $config;

    $apiaddr = '/v3/sign-flow/%s/sign-url';
    $apiaddr = sprintf($apiaddr, $flowId);
    $requestType = HttpEmun::POST;
    $data = [
        'clientType' => 'ALL',
        'needLogin' => false,
        'operator' => [
            'psnAccount' => 'xxxx',
        ],
        'urlType' => 2,
    ];
    $paramStr = json_encode($data);

    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    $url = null;
    if ($response->getStatus() == 200) {
        $url = json_decode($response->getBody())->data->shortUrl;
        EsignLogHelper::printMsg('获取合同文件签署链接调用成功，url: ' . $url);
    } else {
        EsignLogHelper::printMsg('获取合同文件签署链接接口调用失败，HTTP错误码' . $response->getStatus());
    }
    EsignLogHelper::printMsg('**********获取合同文件签署链接调用结束**********');
    return $flowId;
}

/**
 * 获取批量签页面链接（多流程）
 * /v3/sign-flow/batch-sign-url.
 */
function getBatchSignUrl()
{
    global $config;

    $flowId1 = 'xxxx';
    $flowId2 = 'xxxx';
    $operatorId = 'xxxx';
    EsignLogHelper::printMsg('**********获取批量签页面链接开始**********');
    $apiaddr = '/v3/sign-flow/batch-sign-url';
    $requestType = HttpEmun::POST;
    $data = [
        'operatorId' => $operatorId,
        'redirectUrl' => 'http://www.esign.cn',
        'signFlowIds' => [
            $flowId1,
            $flowId2,
        ],
    ];
    $paramStr = json_encode($data);

    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    $url = null;
    if ($response->getStatus() == 200) {
        $url = json_decode($response->getBody())->data->batchSignShortUrlWithoutLogin;
        EsignLogHelper::printMsg('获取批量签页面链接调用成功，url: ' . $url);
    } else {
        EsignLogHelper::printMsg('获取批量签页面链接接口调用失败，HTTP错误码' . $response->getStatus());
    }
    EsignLogHelper::printMsg('**********获取批量签页面链接调用结束**********');
    return $url;
}

/**
 * 下载已签署文件及附属材料
 * /v3/sign-flow/{signFlowId}/file-download-url.
 */
function downloadFile()
{
    global $config;

    $flowId = 'xxxxx';
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

/**
 *  撤销签署流程
 *   /v3/sign-flow/{signFlowId}/revoke.
 */
function revokeFlow()
{
    global $config;

    $flowId = 'xxxx';
    EsignLogHelper::printMsg('**********撤销签署流程开始**********');
    $apiaddr = '/v3/sign-flow/%s/revoke';
    $apiaddr = sprintf($apiaddr, $flowId);
    $requestType = HttpEmun::POST;
    $paramStr = null;
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    if ($response->getStatus() == 200) {
        EsignLogHelper::printMsg('撤销签署流程调用成功: ' . $response->getBody());
    } else {
        EsignLogHelper::printMsg('撤销签署流程调用失败，HTTP错误码' . $response->getStatus());
    }
    EsignLogHelper::printMsg('**********撤销签署流程调用结束**********');
}

/**
 *  催签流程中签署人
 *   /v3/sign-flow/{signFlowId}/urge.
 */
function urgeFlow()
{
    global $config;

    $flowId = 'xxxx';
    EsignLogHelper::printMsg('*********催签流程中签署人开始**********');
    $apiaddr = '/v3/sign-flow/%s/urge';
    $apiaddr = sprintf($apiaddr, $flowId);
    $requestType = HttpEmun::POST;
    $data = [
        'noticeTypes' => '1',
        'urgedOperator' => [
            'psnAccount' => 'xxxx',
        ],
    ];
    $paramStr = json_encode($data);
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    if ($response->getStatus() == 200) {
        EsignLogHelper::printMsg('催签流程中签署人调用成功: ' . $response->getBody());
    } else {
        EsignLogHelper::printMsg('催签流程中签署人调用失败，HTTP错误码' . $response->getStatus());
    }
    EsignLogHelper::printMsg('**********催签流程中签署人调用结束**********');
}

/**
 * 完结签署流程
 * /v3/sign-flow/{signFlowId}/finish.
 */
function finishFlow()
{
    global $config;

    $flowId = 'xxxx';
    EsignLogHelper::printMsg('**********完结签署流程开始**********');
    $apiaddr = '/v3/sign-flow/%s/finish';
    $apiaddr = sprintf($apiaddr, $flowId);
    $requestType = HttpEmun::POST;
    $paramStr = null;
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    if ($response->getStatus() == 200) {
        EsignLogHelper::printMsg('完结签署流程调用成功: ' . $response->getBody());
    } else {
        EsignLogHelper::printMsg('完结签署流程调用失败，HTTP错误码' . $response->getStatus());
    }
    EsignLogHelper::printMsg('**********完结签署流程调用结束**********');
}

/**
 * 查询签署流程详情
 * /v3/sign-flow/{signFlowId}/detail.
 */
function queryFlowDetail()
{
    global $config;

    $flowId = 'xxxx';
    EsignLogHelper::printMsg('**********查询签署流程详情开始**********');
    $apiaddr = '/v3/sign-flow/%s/detail';
    $apiaddr = sprintf($apiaddr, $flowId);
    $requestType = HttpEmun::GET;
    $paramStr = null;
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    if ($response->getStatus() == 200) {
        EsignLogHelper::printMsg('查询签署流程详情调用成功: ' . $response->getBody());
    } else {
        EsignLogHelper::printMsg('查询签署流程详情调用失败，HTTP错误码' . $response->getStatus());
    }
    EsignLogHelper::printMsg('**********查询签署流程详情调用结束**********');
}

/**
 * 查询签署流程列表
 * /v3/sign-flow/sign-flow-list.
 */
function queryFlowList()
{
    global $config;

    EsignLogHelper::printMsg('**********查询签署流程列表开始**********');
    $apiaddr = '/v3/sign-flow/sign-flow-list';
    $requestType = HttpEmun::POST;
    $data = [
        'operator' => [
            'psnAccount' => '16657xxxx',
        ],
        'pageNum' => 1,
        'pageSize' => 20,
        'signFlowStartTimeFrom' => strtotime('-1 months', time()) * 1000,
        'signFlowStartTimeTo' => time() * 1000,
        'signFlowStatus' => [
            1,
            2,
        ],
    ];
    $paramStr = json_encode($data);
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    if ($response->getStatus() == 200) {
        EsignLogHelper::printMsg('查询签署流程列表调用成功: ' . $response->getBody());
    } else {
        EsignLogHelper::printMsg('查询签署流程列表调用失败，HTTP错误码' . $response->getStatus());
    }
    EsignLogHelper::printMsg('**********查询签署流程列表调用结束**********');
}

/**
 * 追加签署区
 * /v3/sign-flow/{signFlowId}/signers/sign-fields.
 */
function addSignFields()
{
    global $config;
    $flowId = 'xxxx';
    $fileId = 'xxxx';
    EsignLogHelper::printMsg('**********追加签署区开始**********');
    $apiaddr = '/v3/sign-flow/%s/signers/sign-fields';
    $apiaddr = sprintf($apiaddr, $flowId);
    $requestType = HttpEmun::POST;
    $data = [
        'signers' => [
            [
                'psnSignerInfo' => [
                    'psnAccount' => 'xxxx',
                ],

                'signFields' => [
                    [
                        'customBizNum' => '123',
                        'fileId' => $fileId,
                        'normalSignFieldConfig' => [
                            'autoSign' => false,
                            'signFieldStyle' => 1,
                            'signFieldPosition' => [
                                'positionPage' => '1',
                                'positionX' => 100,
                                'positionY' => 200,
                            ],
                        ],
                        'signFieldType' => 0,
                    ],
                ],
                'signerType' => 0,
            ],
        ],
    ];
    $paramStr = json_encode($data);
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    if ($response->getStatus() == 200) {
        EsignLogHelper::printMsg('追加签署区调用成功: ' . $response->getBody());
    } else {
        EsignLogHelper::printMsg('追加签署区调用失败，HTTP错误码' . $response->getStatus());
    }
    EsignLogHelper::printMsg('**********追加签署区调用结束**********');
}

/**
 * 删除签署区
 * /v3/sign-flow/{signFlowId}/signers/sign-fields?signFieldIds=xxx1,xxx2.
 */
function delSignFields()
{
    global $config;
    $flowId = 'xxxx';
    $signFieldIds = 'xxxx';
    EsignLogHelper::printMsg('**********删除签署区开始**********');
    $apiaddr = '/v3/sign-flow/%s/signers/sign-fields?signFieldIds=%s';
    $apiaddr = sprintf($apiaddr, $flowId, $signFieldIds);
    $requestType = HttpEmun::DELETE;
    $paramStr = null;
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    if ($response->getStatus() == 200) {
        EsignLogHelper::printMsg('删除签署区调用成功: ' . $response->getBody());
    } else {
        EsignLogHelper::printMsg('删除签署区调用失败，HTTP错误码' . $response->getStatus());
    }
    EsignLogHelper::printMsg('**********删除签署区调用结束**********');
}

/**
 * 核验合同文件签名有效性
 * /v3/files/{fileId}/verify.
 */
function verifySignature()
{
    global $config;
    $flowId = 'xxxx';
    $fileId = 'xxxx';
    EsignLogHelper::printMsg('**********核验合同文件签名有效性开始**********');
    $apiaddr = '/v3/files/%s/verify';
    $apiaddr = sprintf($apiaddr, $fileId);
    $data = [
        'signFlowId' => $flowId,
    ];
    $requestType = HttpEmun::POST;
    $paramStr = json_encode($data);
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    if ($response->getStatus() == 200) {
        EsignLogHelper::printMsg('核验合同文件签名有效性调用成功: ' . $response->getBody());
    } else {
        EsignLogHelper::printMsg('核验合同文件签名有效性调用失败，HTTP错误码' . $response->getStatus());
    }
    EsignLogHelper::printMsg('**********核验合同文件签名有效性调用结束**********');
}

/**
 * 获取区块链存证信息
 * /v3/antchain-file-info.
 */
function queryAntchainFileInfo()
{
    global $config;
    $flowId = 'xxxx';
    EsignLogHelper::printMsg('**********获取区块链存证信息开始**********');
    $apiaddr = '/v3/antchain-file-info';
    $data = [
        'signFlowId' => $flowId,
    ];
    $requestType = HttpEmun::POST;
    $paramStr = json_encode($data);
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    if ($response->getStatus() == 200) {
        EsignLogHelper::printMsg('获取区块链存证信息调用成功: ' . $response->getBody());
    } else {
        EsignLogHelper::printMsg('获取区块链存证信息调用失败，HTTP错误码' . $response->getStatus());
    }
    EsignLogHelper::printMsg('**********获取区块链存证信息调用结束**********');
}

/**
 * 核验区块链存证文件
 * /v3/antchain-file-info/verify.
 */
function verifyAntchainFileInfo()
{
    global $config;
    EsignLogHelper::printMsg('**********核验区块链存证文件开始**********');
    $apiaddr = '/v3/antchain-file-info/verify';
    $data = [
        'fileHash' => 'xxxx',
        'antTxHash' => 'xxxx',
    ];
    $requestType = HttpEmun::POST;
    $paramStr = json_encode($data);
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    if ($response->getStatus() == 200) {
        EsignLogHelper::printMsg('核验区块链存证文件调用成功: ' . $response->getBody());
    } else {
        EsignLogHelper::printMsg('核验区块链存证文件调用失败，HTTP错误码' . $response->getStatus());
    }
    EsignLogHelper::printMsg('**********核验区块链存证文件调用结束**********');
}

/**
 * 获取合同解约链接
 * /v3/sign-flow/{signFlowId}/rescission-url.
 */
function rescissionFlow()
{
    global $config;
    $flowId = 'xxxx';
    EsignLogHelper::printMsg('**********获取合同解约链接开始**********');
    $apiaddr = '/v3/sign-flow/%s/rescission-url';
    $apiaddr = sprintf($apiaddr, $flowId);

    $data = [
        'rescissionInitiator' => [
            'psnInitiator' => [
                'psnId' => 'xxxx',
            ],
        ],
    ];
    $requestType = HttpEmun::POST;
    $paramStr = json_encode($data);
    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    if ($response->getStatus() == 200) {
        EsignLogHelper::printMsg('获取合同解约链接调用成功: ' . $response->getBody());
    } else {
        EsignLogHelper::printMsg('获取合同解约链接调用失败，HTTP错误码' . $response->getStatus());
    }
    EsignLogHelper::printMsg('**********获取合同解约链接调用结束**********');
}

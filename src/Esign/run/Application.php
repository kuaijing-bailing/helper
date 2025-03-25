<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\Esign\run;

include __DIR__ . '/../' . 'EsignOpenAPI.php';
include __DIR__ . '/./' . 'moduleDemo/fileAndTemplate/file.php';

use esign\comm\EsignHttpHelper;
use esign\comm\EsignLogHelper;
use esign\comm\EsignUtilHelper;
use esign\emun\HttpEmun;

class Application
{
    private string $mobile = '';

    private string $organName = '';

    private static bool $debug = false;

    private static bool $logger = false;

    private static array $config = [
        'eSignAppId' => '',
        'eSignAppSecret' => '',
        'eSignHost' => '',
    ];

    public function __construct(array $init, bool $debug = false)
    {
        self::$debug = $debug;
        $this->initConfig($init);
    }

    public function initData(string $mobile, string $organName): static
    {
        $this->mobile = $mobile;
        $this->organName = $organName;
        return $this;
    }

    public function eSignLoggerV3(string|array|object|int $text)
    {
        EsignLogHelper::writeLog($text);
        self::$logger = true;
        return $this;
    }

    public static function ESignDebugV3(string|array|object|int $msg, bool $logger = false)
    {
        self::$debug && EsignLogHelper::printMsg($msg);
        ($logger || self::$logger) && EsignLogHelper::writeLog($msg);
        self::$logger = $logger;
    }

    /*sign*/
    public function platformToBCreateByFile(array $signData)
    {
        self::ESignDebugV3('**********基于文件发起platformToB签署调用开始**********');
        $config = self::$config;
        $mobile = $this->mobile;
        $organName = $this->organName;

        $apiaddr = '/v3/sign-flow/create-by-file';
        $requestType = HttpEmun::POST;
        $paramStr = json_encode($signData);
        self::ESignDebugV3($paramStr);

        $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

        self::ESignDebugV3($signAndBuildSignAndJsonHeader);
        $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);

        self::ESignDebugV3($response->getStatus());
        self::ESignDebugV3($response->getBody());

        $flowId = false;
        if ($response->getStatus() == 200) {
            $result = json_decode($response->getBody());
            if ($result->code == 0) {
                $flowId = $result->data->signFlowId;
                self::ESignDebugV3('基于文件发起签署接口调用成功，flowId: ' . $flowId);
            } else {
                self::ESignDebugV3('基于文件发起签署接口调用失败，错误信息: ' . $result->message);
            }
        } else {
            self::ESignDebugV3('基于文件发起签署接口调用失败，HTTP错误码' . $response->getStatus());
        }
        self::ESignDebugV3('**********基于文件发起签署调用结束**********');

        $responseArray = json_decode($response->getBody());
        return ['response' => self::object_array($responseArray), 'flowId' => $flowId];
    }

    public function platformToCCreateByFile(array $signData)
    {
        self::ESignDebugV3('**********基于文件发起platformToC签署调用开始**********');
        $config = self::$config;
        $mobile = $this->mobile;
        $organName = $this->organName;

        $apiaddr = '/v3/sign-flow/create-by-file';
        $requestType = HttpEmun::POST;

        $paramStr = json_encode($signData);

        $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

        self::ESignDebugV3($signAndBuildSignAndJsonHeader);
        $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
        self::ESignDebugV3($response->getStatus());
        self::ESignDebugV3($response->getBody());

        $flowId = false;
        if ($response->getStatus() == 200) {
            $result = json_decode($response->getBody());
            if ($result->code == 0) {
                $flowId = $result->data->signFlowId;
                self::ESignDebugV3('基于文件发起签署接口调用成功，flowId: ' . $flowId);
            } else {
                self::ESignDebugV3('基于文件发起签署接口调用失败，错误信息: ' . $result->message);
            }
        } else {
            self::ESignDebugV3('基于文件发起签署接口调用失败，HTTP错误码' . $response->getStatus());
        }
        self::ESignDebugV3('**********基于文件发起签署调用结束**********');

        $responseArray = json_decode($response->getBody());
        return ['response' => self::object_array($responseArray), 'flowId' => $flowId];
    }

    /**
     * Notes: createFlowPlatformSign
     * Author: Endness
     * Date: 2024/2/19 20:41.
     */
    public function createByFile(array $signData)
    {
        self::ESignDebugV3('**********基于文件发起签署调用开始**********');

        $config = self::$config;
        $mobile = $this->mobile;
        $organName = $this->organName;
        //global $config ,$mobile,$organName;

        $apiaddr = '/v3/sign-flow/create-by-file';
        $requestType = HttpEmun::POST;
        //上传文件，获取文件id
        //$filePath="/Users/cmn/Sites/V3 Demo/SaaSAPI_V3_Demo_PHP/pdf/test.pdf";
        $filePath = $signData['filePath'] ?? '';
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
                        'orgName' => $organName,
                        'transactorInfo' => [
                            'psnAccount' => $mobile,
                        ],
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
                    'signerType' => 1,
                ],
                [
                    'orgSignerInfo' => [
                        'orgId' => 'xxxxx',    //平台方机构oid
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
        $requestData = $signData['data'] ?? $data;
        $paramStr = json_encode($requestData);

        $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

        self::ESignDebugV3($signAndBuildSignAndJsonHeader);

        $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);

        self::ESignDebugV3($response->getStatus());
        self::ESignDebugV3($response->getBody());

        $flowId = false;
        if ($response->getStatus() == 200) {
            $result = json_decode($response->getBody());
            if ($result->code == 0) {
                $flowId = $result->data->signFlowId;

                self::ESignDebugV3('基于文件发起签署接口调用成功，flowId: ' . $flowId);
            } else {
                self::ESignDebugV3('基于文件发起签署接口调用失败，错误信息: ' . $result->message);
            }
        } else {
            self::ESignDebugV3('基于文件发起签署接口调用失败，HTTP错误码' . $response->getStatus());
        }
        self::ESignDebugV3('**********基于文件发起签署调用结束**********');

        return $flowId;
    }

    public function getSignUrl(string $flowId, array $signData = [])
    {
        self::ESignDebugV3('**********获取合同文件签署链接开始**********');
        $config = self::$config;
        $mobile = $this->mobile;

        $apiaddr = '/v3/sign-flow/%s/sign-url';
        $apiaddr = sprintf($apiaddr, $flowId);
        $requestType = HttpEmun::POST;
        $data = [
            'clientType' => 'ALL',
            'needLogin' => false,
            'operator' => [
                'psnAccount' => $mobile,
            ],
            'urlType' => 2,
        ];
        $requestData = ! empty($signData) ? $signData : $data;
        $paramStr = json_encode($requestData);

        $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

        self::ESignDebugV3($signAndBuildSignAndJsonHeader);
        $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);

        self::ESignDebugV3($response->getStatus());
        self::ESignDebugV3($response->getBody());
        $url = null;
        if ($response->getStatus() == 200) {
            $url = json_decode($response->getBody())->data->shortUrl;
            self::ESignDebugV3('获取合同文件签署链接调用成功，url: ' . $url);
        } else {
            self::ESignDebugV3('获取合同文件签署链接接口调用失败，HTTP错误码' . $response->getStatus());
        }
        self::ESignDebugV3('**********获取合同文件签署链接调用结束**********');
        $responseArray = json_decode($response->getBody());
        return ['response' => self::object_array($responseArray), 'shortUrl' => $url];
    }

    public function downloadFile(string $flowId)
    {
        $config = self::$config;

        self::ESignDebugV3('**********下载已签署文件及附属材料开始**********');
        $apiaddr = '/v3/sign-flow/%s/file-download-url';
        $apiaddr = sprintf($apiaddr, $flowId);
        $requestType = HttpEmun::GET;
        $paramStr = null;
        $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

        //EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
        self::ESignDebugV3($signAndBuildSignAndJsonHeader);
        $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);

        self::ESignDebugV3($response->getStatus());
        self::ESignDebugV3($response->getBody());
        if ($response->getStatus() == 200) {
            self::ESignDebugV3('下载已签署文件及附属材料调用成功: ' . $response->getBody());
        } else {
            self::ESignDebugV3('下载已签署文件及附属材料调用失败，HTTP错误码' . $response->getStatus());
        }
        self::ESignDebugV3('**********下载已签署文件及附属材料调用结束**********');

        $responseArray = json_decode($response->getBody());

        return self::object_array($responseArray);
    }

    /*File*/
    public function fileUploadUrl($filePath, $convert2Pdf = true): array
    {
        $config = self::$config;
        $apiaddr = '/v3/files/file-upload-url';
        $requestType = HttpEmun::POST;

        $filename = basename($filePath);
        $filesize = ! empty(filesize($filePath)) ? filesize($filePath) : '';

        if (empty($filesize)) {
            $fileContent = file_get_contents($filePath);
            $filesize = strlen($fileContent);
        }
        $data = [
            'contentMd5' => EsignUtilHelper::getContentBase64Md5($filePath),
            'contentType' => 'application/pdf',
            'convertToPDF' => $convert2Pdf,
            'fileName' => $filename,
            'fileSize' => $filesize,
        ];
        $paramStr = json_encode($data);
        //生成签名验签+json体的header

        $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);
        //获取文件上传地址
        self::ESignDebugV3('=========获取文件上传地址=========');
        self::ESignDebugV3($signAndBuildSignAndJsonHeader);

        $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
        self::ESignDebugV3($response->getStatus());
        self::ESignDebugV3('=========获取文件上传结果=========');
        self::ESignDebugV3($response->getBody());

        $fileUploadUrl = json_decode($response->getBody())->data->fileUploadUrl;
        $fileId = json_decode($response->getBody())->data->fileId;
        //文件流put上传
        $response = EsignHttpHelper::upLoadFileHttp($fileUploadUrl, $filePath, 'application/pdf');
        self::ESignDebugV3($response->getStatus());
        self::ESignDebugV3($response->getBody());
        $responseArray = json_decode($response->getBody());

        return ['uploadRes' => self::object_array($responseArray), 'fileId' => $fileId, 'fileUploadUrl' => $fileUploadUrl];
    }

    /**
     * 查询机构认证信息.
     */
    public function organizationsInfo(string $nameOrg, string $idTypeOrg, string $idNumberOrg)
    {
        $config = self::$config;

        $apiAddr = sprintf('/v3/organizations/identity-info?orgIDCardNum=%s', $nameOrg);
        $requestType = HttpEmun::GET;
        //生成签名验签+json体的header

        $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], '', $requestType, $apiAddr);
        //发起接口请求
        self::ESignDebugV3($signAndBuildSignAndJsonHeader);
        $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiAddr, $requestType, $signAndBuildSignAndJsonHeader, '');
        $responseArray = json_decode($response->getBody());

        self::ESignDebugV3($config['eSignHost'] . $apiAddr);
        self::ESignDebugV3($response->getStatus());
        self::ESignDebugV3('==========查询机构认证信息==========');
        self::ESignDebugV3($response->getBody());

        return self::object_array($responseArray);
    }

    /**
     * Notes: 查询文件状态信息
     * Author: Endness
     * Date: 2024/2/21 14:13.
     * @return mixed
     */
    public function fileStatues(string $fileId)
    {
        $config = self::$config;

        $apiaddr = '/v3/files/' . $fileId;
        $requestType = HttpEmun::GET;
        //生成签名验签+json体的header

        $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], '', $requestType, $apiaddr);
        //发起接口请求
        self::ESignDebugV3($signAndBuildSignAndJsonHeader);
        $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, '');
        $responseArray = json_decode($response->getBody());

        self::ESignDebugV3($response->getStatus());
        self::ESignDebugV3('==========文件状态结果==========');
        self::ESignDebugV3($response->getBody());

        return self::object_array($responseArray);
    }

    /**
     * Notes: E签宝检索PDF文件中所含关键字的所有XY坐标信息
     * Author: Endness
     * Date: 2024/2/21 15:04.
     * @param mixed $fileId
     * @param mixed $keywords
     */
    public function searchWordsPosition($fileId, $keywords = [])
    {
        $config = self::$config;

        $apiaddr = '/v3/files/%s/keyword-positions';
        $apiaddr = sprintf($apiaddr, $fileId);

        $requestType = HttpEmun::POST;

        $data = [
            'keywords' => $keywords ?: ['${甲方签名处}', '${乙方签名处}'],
        ];
        $paramStr = json_encode($data);
        //生成签名验签+json体的header

        $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);
        //发起接口请求
        self::ESignDebugV3($signAndBuildSignAndJsonHeader);
        $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
        $responseArray = json_decode($response->getBody());

        self::ESignDebugV3($response->getStatus());
        self::ESignDebugV3('==========网关响应结果==========');
        self::ESignDebugV3($response->getBody());

        return self::object_array($responseArray);
    }

    public function searchWordsPositionGet($fileId, $keywords)
    {
        $config = self::$config;

        $apiaddr = '/v3/files/%s/keyword-positions?keywords=' . $keywords;
        $apiaddr = sprintf($apiaddr, $fileId);
        $requestType = HttpEmun::GET;
        //生成签名验签+json体的header

        $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], '', $requestType, $apiaddr);

        //发起接口请求
        self::ESignDebugV3($signAndBuildSignAndJsonHeader);
        $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, '');
        $responseArray = json_decode($response->getBody());

        self::ESignDebugV3($response->getStatus());
        self::ESignDebugV3('==========网关响应结果==========');
        self::ESignDebugV3($response->getBody());

        return self::object_array($responseArray);
    }

    /*signFlow*/
    public function queryFlowDetail($flowId)
    {
        $config = self::$config;
        self::ESignDebugV3('**********查询签署流程详情开始**********');

        $apiaddr = '/v3/sign-flow/%s/detail';
        $apiaddr = sprintf($apiaddr, $flowId);
        $requestType = HttpEmun::GET;
        $paramStr = null;
        $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);

        self::ESignDebugV3($signAndBuildSignAndJsonHeader);
        $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);

        self::ESignDebugV3($response->getStatus());
        self::ESignDebugV3($response->getBody());
        if ($response->getStatus() == 200) {
            self::ESignDebugV3('查询签署流程详情调用成功: ' . $response->getBody());
        } else {
            self::ESignDebugV3('查询签署流程详情调用失败，HTTP错误码' . $response->getStatus());
        }
        self::ESignDebugV3('**********查询签署流程详情调用结束**********');
        $responseArray = json_decode($response->getBody());
        return self::object_array($responseArray);
    }

    /*seals*/
    public function queryOrgSeals(string $orgId)
    {
        $config = self::$config;
        $apiaddr = '/v3/seals/org-own-seal-list?orgId=' . $orgId . '&pageNum=1&pageSize=20';
        $requestType = HttpEmun::GET;
        //生成签名验签+json体的header
        $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], '', $requestType, $apiaddr);

        //发起接口请求
        self::ESignDebugV3($signAndBuildSignAndJsonHeader);
        $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, '');
        self::ESignDebugV3($response->getStatus());
        $code = json_decode($response->getBody())->code;
        if ($code === 0) {
            self::ESignDebugV3('查询企业内部印章接口调用成功');
            self::ESignDebugV3($response->getBody());
        } else {
            self::ESignDebugV3($response->getBody());
        }
        $responseArray = json_decode($response->getBody());
        return self::object_array($responseArray);
    }

    /*auth*/
    public function authUrl(array $authData)
    {
        $config = self::$config;
        $apiaddr = '/v3/org-auth-url';
        $requestType = HttpEmun::POST;
        $data = ! empty($authData) ? $authData : [
            'orgAuthConfig' => [
                'orgName' => '',
                'orgInfo' => [
                    'orgIDCardNum' => '',
                    'orgIDCardType' => 'CRED_ORG_USCC',
                ],
                'transactorInfo' => [
                    'psnAccount' => '',
                    'psnInfo' => [
                        'psnIDCardNum' => '',
                        'psnName' => '',
                    ],
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

        self::ESignDebugV3($signAndBuildSignAndJsonHeader);
        $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
        self::ESignDebugV3($response->getStatus());
        self::ESignDebugV3($response->getBody());
        $responseArray = json_decode($response->getBody());
        return self::object_array($responseArray);
    }

    private function initConfig(array $config)
    {
        if (! empty($config['eSignAppId']) && ! empty($config['eSignAppSecret']) && ! empty($config['eSignHost'])) {
            self::$config = ['eSignAppId' => $config['eSignAppId'], 'eSignAppSecret' => $config['eSignAppSecret'], 'eSignHost' => $config['eSignHost']];
        }
        ! empty($config['mobile']) && $this->mobile = $config['mobile'];
        ! empty($config['organName']) && $this->organName = $config['organName'];
    }

    private static function object_array($array)
    {
        if (is_object($array)) {
            $array = (array) $array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = self::object_array($value);
            }
        }
        return $array;
    }
}

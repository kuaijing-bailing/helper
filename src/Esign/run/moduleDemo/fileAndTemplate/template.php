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
include 'file.php';
$config = Config::$config;
/**
 * 模板服务API.
 * @date  2022/09/02 9:51
 */

//场景：1上传文件即为待签署文件；2上传文件创建pdf模板;3制作HTML动态表格模板；4查询文件状态
$scence = 1;    //场景编号

$filePath = 'D:\\file\\1.docx';
$contentMd5 = contentMd5($filePath);
echo $contentMd5;
switch ($scence) {
    case '1':
        //上传文件即为待签署文件
        $fileId = fileUploadUrl($filePath, $contentMd5);
        break;
    case '2':
        //上传文件创建pdf模板
        $fileId = fileUploadUrl($filePath, $contentMd5);
        $fileStatus = fileStatues($fileId);
        //文件转pdf为异步
        if ($fileStatus != 2 or $fileStatus != 5) {
            EsignLogHelper::printMsg('docx转PDF中，请稍后...');
            sleep(5);
            $fileStatus = fileStatues($fileId);
        }
        ////获取制作合同模板页面
        docTemplateCreateUrl($fileId, 0);
        $docTemplateId = '7dce36f2384f40669f7625f2fadXXXX';
        //查询合同模板中控件详情
        templatesTComponents($docTemplateId);
        //填写pdf模板生成文件
        $fileId = createByDocTemplate($docTemplateId);
        break;
    case '3':
        //制作HTML动态表格模板
        $fileId = fileUploadToHtmlUrl($filePath, $contentMd5);
        $fileStatus = fileStatues($fileId);
        //文件转换HTML异步
        if ($fileStatus != 2) {
            EsignLogHelper::printMsg('docx转HTML中，请稍后...');
            sleep(5);
            $fileStatus = fileStatues($fileId);
        }
        docTemplateCreateUrl($fileId, 1);
        $docTemplateId = '7dce36f2384f40669f7625f2fadXXXX';
        //查询合同模板中控件详情
        templatesTComponents($docTemplateId);
        //动态表格填充内容生成pdf
        $fileId = createByDocHTMLTemplate($docTemplateId);
        $fileStatus = fileStatues($fileId);
        //生成后pdf后，e签宝自动上传oss为异步
        if ($fileStatus != 2) {
            EsignLogHelper::printMsg('动态表格文件已生成，上传中,请稍后...');
            sleep(5);
            $fileStatus = fileStatues($fileId);
        }
        break;
    case '4':
        //查询文件状态
        $fileId = '3a77485c5312487ab4a6246a7b93XXXX';
        fileStatues($fileId);
        break;
    default:
        EsignLogHelper::printMsg('场景选择错误');
}

/**
 * 填写pdf模板生成文件.
 * @param $docTemplateId
 * @return mixed
 */
function createByDocTemplate($docTemplateId)
{
    global $config;
    $apiaddr = '/v3/files/create-by-doc-template';
    $requestType = HttpEmun::POST;
    $data = [
        'docTemplateId' => $docTemplateId,
        'fileName' => '填写PDF文件测试',
        'components' => [
            [
                'componentKey' => 'username',
                'componentValue' => '张三',
                'requiredCheck' => false,
            ],
        ],
    ];
    $paramStr = json_encode($data);
    //生成签名验签+json体的header

    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);
    //发起接口请求
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    $fileId = json_decode($response->getBody())->data->fileId;
    return $fileId;
}

/**
 * 动态表格填充内容生成pdf.
 * @param $docTemplateId
 * @return mixed
 */
function createByDocHTMLTemplate($docTemplateId)
{
    global $config;
    $apiaddr = '/v3/files/create-by-doc-template';
    $requestType = HttpEmun::POST;
    $data = [
        'docTemplateId' => $docTemplateId,
        'fileName' => '动态表格填充测试',
        'components' => [
            [
                'componentKey' => 'table',
                'componentValue' => '[{"row":{"column1":"姓名","column2":"联系电话","column3":"家庭住址","column4":"健康情况"}},{"row":{"column1":"e签宝","column2":"0571-XXXXX","column3":"杭州","column4":"健康"}},{"insertRow":"true","row":{"column1":"张三","column2":"","column3":"北京","column4":"亚健康"}},{"insertRow":"true","row":{"column1":"李四","column2":"13000000000","column3":"上海","column4":"不健康"}},{"insertRow":"true","row":{"column1":"王五","column2":"","column3":"广州","column4":"可能健康"}},{"insertRow":"true","row":{"column1":"不知道","column2":"","column3":"深圳","column4":"健康么"}}]',
                'requiredCheck' => false,
            ],
            [
                'componentKey' => 'username',
                'componentValue' => '张三',
                'requiredCheck' => false,
            ],
            [
                'componentKey' => 'address',
                'componentValue' => '浙江省杭州市西湖区XXX',
                'requiredCheck' => false,
            ],
        ],
    ];
    $paramStr = json_encode($data);
    //生成签名验签+json体的header

    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);
    //发起接口请求
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    $fileId = json_decode($response->getBody())->data->fileId;
    return $fileId;
}

/**
 * 查询合同模板中控件详情.
 * @param $docTemplateId
 */
function templatesTComponents($docTemplateId)
{
    global $config;
    $apiaddr = '/v3/doc-templates/' . $docTemplateId;
    $requestType = HttpEmun::GET;
    //生成签名验签+json体的header

    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], '', $requestType, $apiaddr);
    //发起接口请求
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, '');
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
}

/**
 * 获取制作合同模板页面.
 * @param $fileId
 * @param $docTemplateType
 * @return mixed
 */
function docTemplateCreateUrl($fileId, $docTemplateType)
{
    global $config;
    $apiaddr = '/v3/doc-templates/doc-template-create-url';
    $requestType = HttpEmun::POST;
    $data = [
        'docTemplateName' => '模板文件测试',
        'docTemplateType' => $docTemplateType,
        'fileId' => $fileId,
        'redirectUrl' => '',
    ];
    $paramStr = json_encode($data);
    //生成签名验签+json体的header

    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], $paramStr, $requestType, $apiaddr);
    //发起接口请求
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, $paramStr);
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    $docTemplateId = json_decode($response->getBody())->data->docTemplateId;
    return $docTemplateId;
}

/**
 * 查询文件状态
 * @param $fileId
 * @return mixed
 */
function fileStatues($fileId)
{
    global $config;

    $apiaddr = '/v3/files/' . $fileId;
    $requestType = HttpEmun::GET;
    //生成签名验签+json体的header

    $signAndBuildSignAndJsonHeader = EsignHttpHelper::signAndBuildSignAndJsonHeader($config['eSignAppId'], $config['eSignAppSecret'], '', $requestType, $apiaddr);
    //发起接口请求
    EsignLogHelper::printMsg($signAndBuildSignAndJsonHeader);
    $response = EsignHttpHelper::doCommHttp($config['eSignHost'], $apiaddr, $requestType, $signAndBuildSignAndJsonHeader, '');
    EsignLogHelper::printMsg($response->getStatus());
    EsignLogHelper::printMsg($response->getBody());
    $fileStatus = $fileId = json_decode($response->getBody())->data->fileStatus;
    return $fileStatus;
}

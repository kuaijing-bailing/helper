<?php
namespace esign\comm;

/**
 * esignHttp请求类
 * @author  澄泓
 * @date  2022/08/18 15:10
 */
class EsignHttpHelper
{
    /**
     * 常规请求
     * @param $host
     * @param $url
     * @param $reqType
     * @param $header
     * @param $paramStr
     * @return EsignResponse
     */
    public static function doCommHttp($host, $url, $reqType, $header, $paramStr)
    {
        //做encode编码防止中文乱码
        $url = EsignHttpHelper::querySort($url, true);
        return EsignHttpCfgHelper::sendHttp($reqType, $host . $url, $header, $paramStr);
    }

    /**
     * 文件上传
     * @param $uploadUrls
     * @param $filePath
     * @param $ContenType
     * @return EsignResponse
     */
    public static function upLoadFileHttp($uploadUrls, $filePath, $ContenType)
    {
        $fileContent = file_get_contents($filePath);
        $contentBase64Md5 = EsignUtilHelper::getContentBase64Md5($filePath);
        return EsignHttpCfgHelper::upLoadFileHttp($uploadUrls, $contentBase64Md5, $fileContent, $ContenType);
    }


    /**
     * 签名计算并且构建一个签名鉴权+json数据的esign请求头
     * @param $projectId
     * @param $secret
     * @param $paramStr
     * @param $reqType
     * @param $url
     * @param string $accept
     * @param string $contentType
     * @return array
     */
    public static function signAndBuildSignAndJsonHeader($projectId, $secret, $paramStr, $reqType, $url,$accept="*/*",$contentType="application/json; charset=UTF-8")
    {
        $contentMd5="";

        //get和delete方式请求不能携带body体
        if (strtoupper($reqType) == "GET" || strtoupper($reqType) == "DELETE") {
            $paramStr=null;
        }else{
            $contentMd5 = EsignUtilHelper::getContentMd5($paramStr);
        }
        //对body体做md5摘要

        //构造一个初步请求头
        $buildSignAndJsonHeader = self::buildSignAndJsonHeader($projectId, $contentMd5, $accept, $contentType, "Signature");

        //对url做排序
        self::querySort($url);
        //传入生成的bodyMd5,加上其他请求头部信息拼接成字符串,整体做sha256签名
        $reqSignature = EsignUtilHelper::getSignature($secret, $reqType, $accept, $contentType, $contentMd5, "", "", $url);
        array_push($buildSignAndJsonHeader,"X-Tsign-Open-Ca-Signature:".$reqSignature);
        return $buildSignAndJsonHeader;
}

    /**
     * 建一个签名鉴权+json数据的esign初步请求头
     * @param $projectId
     * @param $contentMD5
     * @param $accept
     * @param $contentType
     * @param $authMode
     * @return array
     */
    public static function buildSignAndJsonHeader($projectId, $contentMD5, $accept, $contentType, $authMode)
    {
        $headers = array(
            'X-Tsign-Open-App-Id:'.$projectId,
            'X-Tsign-Open-Ca-Timestamp:'.EsignUtilHelper::getMillisecond(),
            'Accept:'.$accept,
            'Content-Type:'.$contentType,
            'X-Tsign-Open-Auth-Mode:'.$authMode,
            'X-Tsign-Dns-App-Id:'.$projectId
        );
        if(!empty($contentMD5)){
            array_push($headers,'Content-MD5:'.$contentMD5);
        }
        return $headers;
    }


    public static function querySort($url,$isEncode=false){
        #存在query参数
        if (!strpos($url, "?") === false) {
            $query_arr = [];
            $url_path = substr($url, 0, strpos($url, "?"));
            $url_query = substr($url, strpos($url, "?") + 1, mb_strlen($url));
            parse_str($url_query, $query_arr);
            ksort($query_arr);
            //reset()内部指针指向数组中的第一个元素
            reset($query_arr);
            $query_str = http_build_query($query_arr);
            if(!$isEncode){
                $query_str = urldecode($query_str);
            }
            $url = $url_path . '?' . $query_str;
            return $url;
        }
        return $url;
    }
    /**
     * 构造头部信息
     * @param $contentMD5
     * @param $reqSignature
     * @return array
     */
    public static function buildCommHeader($projectId, $contentMD5, $reqSignature)
    {
        $headers = array(
            'X-Tsign-Open-App-Id:' . $projectId,
            'X-Tsign-Open-Ca-Timestamp:' . EsignUtilHelper::getMillisecond(),
            'Accept:*/*',
            'X-Tsign-Open-Ca-Signature:' . $reqSignature,
            'Content-MD5:' . $contentMD5,
            'Content-Type:application/json; charset=UTF-8',
            'X-Tsign-Open-Auth-Mode:Signature'
        );
        return $headers;
    }

}

<?php
namespace esign\comm;
/**
 * 网络请求配置工具类
 * @author  澄泓
 * @date  2022/08/18 14:27
 */
class EsignHttpCfgHelper
{
    public static $connectTimeout = 15;//15 second
    public static $readTimeout = 15;//15 second
    public static $uploadReadTimeout = 60;
    public static $uploadConnectTimeout = 60;
    public static $enableHttpProxy=false;//是否需要代理
    public static $httpProxyIp;//代理ip
    public static $httpProxyPort;//代理端口

    //常规请求类
    public static function sendHttp($reqType, $url, $headers, $param=null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $reqType);
        if (self::$enableHttpProxy) {
            curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_PROXY, self::$httpProxyIp);
            curl_setopt($ch, CURLOPT_PROXYPORT, self::$httpProxyPort);
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $postData = is_array($param) ? json_encode($param, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $param;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        if (self::$readTimeout) {
            curl_setopt($ch, CURLOPT_TIMEOUT, self::$readTimeout);
        }
        if (self::$connectTimeout) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::$connectTimeout);
        }
        //https request
        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == "https") {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        if (is_array($headers) && 0 < count($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        $curlRes = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return new EsignResponse($httpCode,$curlRes);
    }

    /**
     * 上传文件
     * @param $uploadUrls
     * @param $contentMd5
     * @param $fileContent
     * @param $ContenType
     * @return EsignResponse
     */
        public static function upLoadFileHttp($uploadUrls, $contentMd5, $fileContent,$ContenType){
            $header = array(
                'Content-Type:'.$ContenType,
                'Content-Md5:' . $contentMd5
            );

            $curl_handle = curl_init();
            curl_setopt($curl_handle, CURLOPT_URL, $uploadUrls);
            curl_setopt($curl_handle, CURLOPT_FILETIME, true);
            curl_setopt($curl_handle, CURLOPT_FRESH_CONNECT, false);
           // curl_setopt($curl_handle, CURLOPT_HEADER, true); // 输出HTTP头 true
            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);

            if (self::$uploadReadTimeout) {
                curl_setopt($curl_handle, CURLOPT_TIMEOUT, self::$uploadReadTimeout);
            }
            if (self::$uploadConnectTimeout) {
                curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, self::$uploadConnectTimeout);
            }
            
            curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, false);

            curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, 'PUT');

            curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $fileContent);
            if (is_array($header) && 0 < count($header)) {
                curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $header);
            }
            $curlRes = curl_exec($curl_handle);
            $httpCode = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);

            curl_close($curl_handle);
            return new EsignResponse($httpCode,$curlRes);
    }

    
}

<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
callback();
//签署回调
function callback()
{
    $secret = 'xxxxx'; //项目对应密钥

    //    此处可以打印下日志
    $file = fopen('callback.log', 'a');
    fwrite($file, "startTime\n" . date('Y-m-d H:i:s'));

    file_get_contents('php://input');

    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        fwrite($file, '非法回调');
        exit;
    }
    fwrite($file, json_encode($_SERVER));

//    校验签名 如果header里放入的值为X_TSIGN_OPEN_SIGNATURE，到header里会自动加上HTTP_，并且转化为大写，取值时如下
    if (! isset($_SERVER['HTTP_X_TSIGN_OPEN_SIGNATURE'])) {
        echo "签名不能为空\n";
        exit;
    }
    $sign = $_SERVER['HTTP_X_TSIGN_OPEN_SIGNATURE'];
    fwrite($file, 'sign:' . $sign);

    //1.获取时间戳的字节流
    if (! isset($_SERVER['HTTP_X_TSIGN_OPEN_TIMESTAMP'])) {
        echo "时间戳不能为空\n";
        exit;
    }
    $timeStamp = $_SERVER['HTTP_X_TSIGN_OPEN_TIMESTAMP'];

    //2.获取query请求的字节流，对 Query 参数按照字典对 Key 进行排序后,按照value1+value2方法拼接
    $params = $_GET;
    if (! empty($params)) {
        ksort($params);
    }

    $requestQuery = '';
    foreach ($params as $val) {
        $requestQuery .= $val;
    }
    fwrite($file, '获取query的数据:' . $requestQuery . "\n");

    //3. 获取body的数据
    $body = file_get_contents('php://input');
    fwrite($file, '获取body的数据:' . $body . "\n");

    //4.组装数据并计算签名
    $data = $timeStamp . $requestQuery . $body;
    fwrite($file, '组装数据并计算签名:' . $data . "\n");

    var_dump($data);
    echo "\n";

    echo $sign . "\n";
    $mySign = hash_hmac('sha256', $data, $secret);

    echo $mySign . "\n";
    if ($mySign != $sign) {
        echo '验签失败';
        fwrite($file, "签名校验失败\n");
    } else {
        echo '验签成功';
    }

    $result = json_decode($body, true);
    switch ($result['action']) {
        case 'SIGN_MISSON_COMPLETE':
            //签署人签署完成回调
            break;
        case 'SIGN_FLOW_COMPLETE':
            //流程结束逻辑处理
            break;
    }
}

<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace esign\comm;

/**
 * esign日志类.
 * @date  2022/08/18 15:10
 */
class EsignLogHelper
{
    public static function writeLog($text)
    {
        if (is_array($text) || is_object($text)) {
            $text = json_encode($text);
        }
        file_put_contents('../../log/' . date('Y-m-d') . '.log', date('Y-m-d H:i:s') . '  ' . $text . "\r\n", FILE_APPEND);
    }

    public static function printMsg($msg)
    {
        stdLog()->info('EsignLog printMsg', ['msg' => $msg]);
    }
}

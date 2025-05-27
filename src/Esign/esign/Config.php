<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace esign;

class Config
{
    public static $config = [
        'eSignAppId' => 'XXXXX', //app id
        'eSignAppSecret' => 'XXXXXX', // app key
        'eSignHost' => 'https://smlopenapi.esign.cn', //模拟环境
        //     'eSignHost'=>'https://openapi.esign.cn' //正式环境
    ];
}

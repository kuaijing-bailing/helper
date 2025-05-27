<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
//session_start();
//生产环境，请禁用错误报告
//error_reporting(0);
error_reporting();

//定义DEMO相关目录，不要随意修改
define('ESIGN_ROOT', __DIR__);
//define("ESIGN_CLASS_PATH", ESIGN_ROOT . "/core/");

//调试模式，false：不打印相关日志；true、请设置日志文件目录以及读写权限
define('ESIGN_DEBUGE', true);

//日志文件目录
define('ESIGN_LOG_DIR', realpath(ESIGN_ROOT . '/../') . '/logs/');
if (ESIGN_DEBUGE && ! is_dir(ESIGN_LOG_DIR)) {
    mkdir(ESIGN_LOG_DIR, 0777);
}

//sdk类文件自动加载
spl_autoload_register(function ($class) {
    $class_path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $class_file = ESIGN_ROOT . DIRECTORY_SEPARATOR . $class_path . '.php';
    if (is_file($class_file)) {
        require_once $class_file;
    }
});

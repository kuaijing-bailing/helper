<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\Event;

/**
 * 清理缓存文件之后调用的事件，便于各业务监听事件清理其他的数据，例如数据库删除缓存数据，免得自行再注册其他定时任务执行.
 */
class RuntimeFileClear
{
    public function __construct()
    {
    }
}

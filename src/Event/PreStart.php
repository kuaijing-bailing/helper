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
 * bailing 包预启动逻辑执行完成后触发，便于业务服务在独立命令进程中完成启动初始化.
 */
class PreStart {}

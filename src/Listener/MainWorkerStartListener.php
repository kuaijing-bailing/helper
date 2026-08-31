<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */

namespace Bailing\Listener;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\MainWorkerStart;

/**
 * Hyperf worker 启动后执行.
 */
#[Listener]
class MainWorkerStartListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            MainWorkerStart::class,
        ];
    }

    public function process(object $event): void
    {
        // 使用独立命令进程执行启动逻辑，命令结束后即可释放初始化代码及其依赖占用的内存。
        $command = sprintf(
            '%s %s %s',
            escapeshellarg(PHP_BINARY),
            escapeshellarg(BASE_PATH . '/bin/hyperf.php'),
            escapeshellarg('preStart')
        );
        passthru($command, $exitCode);
        stdLog()->info('preStart result:', [$exitCode]);
    }
}

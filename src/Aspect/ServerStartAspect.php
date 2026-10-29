<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */

namespace Bailing\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Server\ServerFactory;
use Symfony\Component\Process\Process;

/** 在所有服务器初始化前完成迁移，统一阻断进程模式及协程多端口模式的启动。 */
#[Aspect]
class ServerStartAspect extends AbstractAspect
{
    /** 服务工厂初始化入口；此时尚未创建服务器、消费者及各端口的启动协程。 */
    public array $classes = [ServerFactory::class . '::configure'];

    /** 同步检查成功后才进入服务初始化；异常直接交给启动命令返回失败。 */
    public function process(ProceedingJoinPoint $proceedingJoinPoint): mixed
    {
        // MainCoroutineServerStart 已在首个端口协程内，等待子进程会放行其他端口，必须提前检查。
        $process = $this->createProcess();
        $process->mustRun(static function (string $type, string $buffer) use ($process): void {
            fwrite($type === Process::ERR ? STDERR : STDOUT, $buffer);
            $process->clearOutput();
            $process->clearErrorOutput();
        });
        return $proceedingJoinPoint->process();
    }

    /** 独立运行预启动命令，等待完成且不限制迁移耗时。 */
    protected function createProcess(): Process
    {
        return new Process([PHP_BINARY, BASE_PATH . '/bin/hyperf.php', 'preStart', '--no-interaction'], null, null, null, null);
    }
}

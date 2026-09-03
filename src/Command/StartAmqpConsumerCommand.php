<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */

namespace Bailing\Command;

use Hyperf\Amqp\Consumer;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Process\ProcessManager;
use Swoole\Coroutine\System;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class StartAmqpConsumerCommand extends HyperfCommand
{
    public const COMMAND_NAME = 'start:amqpConsumer';

    private const RESTART_DELAY_SECONDS = 3;

    public function __construct()
    {
        parent::__construct('start:amqpConsumer');
    }

    public function configure()
    {
        parent::configure();
        $this->addOption('mqClass', 'm', InputOption::VALUE_REQUIRED, 'MQ类名');
        $this->setDescription('手动启动mq消费者');
    }

    public function handle()
    {
        $mqClass = $this->input->getOption('mqClass');

        ProcessManager::setRunning(true);

        stdLog()->info('启动mq消费者(' . $mqClass . ')...');
        $consumer = container()->get(Consumer::class);

        // 消费者达到 max_consumption 或连接异常时会返回，循环可确保手动启动的消费者持续在线。
        while (ProcessManager::isRunning()) {
            try {
                $consumer->consume(make($mqClass));
                if (! ProcessManager::isRunning()) {
                    break;
                }

                stdLog()->warning('mq消费者退出，准备重新启动：' . $mqClass);
            } catch (\Throwable $throwable) {
                stdLog()->warning('mq消费者异常，准备重试：' . $mqClass, [
                    'exception' => $throwable::class,
                    'message' => $throwable->getMessage(),
                ]);
            }

            if (ProcessManager::isRunning()) {
                System::sleep(self::RESTART_DELAY_SECONDS);
            }
        }
    }
}

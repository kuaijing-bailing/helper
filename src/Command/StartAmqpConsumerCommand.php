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
        container()->get(Consumer::class)->consume(make($mqClass));

        // mq消费者会自行阻塞进程，无需处理。
    }
}

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

use Bailing\Helper\Webhook\WebhookInvokeHelper;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\MainWorkerStart;

use function Hyperf\Support\retry;

/**
 * Hyperf worker 启动后执行.
 */
#[Listener]
class MainWorkerStartListener implements ListenerInterface
{
    private const WEBHOOK_RETRY_BACKOFF_MILLISECONDS = [500, 1000, 2000];

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
        stdLog()->info('preStart result:', ['command' => $command, 'exitCode' => $exitCode]);

        // 主 Worker 启动阶段使用 Hyperf AMQP 连接池发送 webhook，避免命令行进程重复建立 RabbitMQ 连接。
        $webhookInvokeHelper = new WebhookInvokeHelper();

        stdLog()->info('registerServiceWebhook');
        retry(self::WEBHOOK_RETRY_BACKOFF_MILLISECONDS, static function (int $attempt) use ($webhookInvokeHelper): void {
            try {
                $webhookInvokeHelper->registerServiceWebhook();
            } catch (\Throwable $throwable) {
                stdLog()->warning('registerServiceWebhook attempt failed', [
                    'attempt' => $attempt,
                    'exception' => $throwable::class,
                    'message' => $throwable->getMessage(),
                ]);
                throw $throwable;
            }
        });
        stdLog()->info('registerServiceWebhook completed');

        stdLog()->info('registerServiceWebhookNode');
        retry(self::WEBHOOK_RETRY_BACKOFF_MILLISECONDS, static function (int $attempt) use ($webhookInvokeHelper): void {
            try {
                $webhookInvokeHelper->registerServiceWebhookNode();
            } catch (\Throwable $throwable) {
                stdLog()->warning('registerServiceWebhookNode attempt failed', [
                    'attempt' => $attempt,
                    'exception' => $throwable::class,
                    'message' => $throwable->getMessage(),
                ]);
                throw $throwable;
            }
        });
        stdLog()->info('registerServiceWebhookNode completed');
    }
}

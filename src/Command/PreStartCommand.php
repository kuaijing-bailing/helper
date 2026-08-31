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

use Bailing\Event\PreStart;
use Bailing\Helper\Annotation\I18nTranslationReportHelper;
use Bailing\Helper\Annotation\TranslationReportHelper;
use Bailing\Helper\Webhook\WebhookInvokeHelper;
use Bailing\Helper\XxlJobTaskHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Annotation\Producer;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Di\Annotation\AnnotationCollector;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Process;

#[Command]
class PreStartCommand extends HyperfCommand
{
    private const CACHE_KEY_PREFIX = 'service_auto:pre_start_command:';

    private const RUNNING_CACHE_TTL_SECONDS = 900;

    private const COMPLETED_CACHE_TTL_SECONDS = 3;

    public function __construct()
    {
        parent::__construct('preStart');
    }

    public function handle(): void
    {
        $redis = redis();
        $cacheKey = self::CACHE_KEY_PREFIX . strval(env('MICRO_NAME', env('APP_NAME')));

        // Docker 可能在启动服务前主动执行该命令，使用服务级缓存避免与启动监听器重复执行。
        if (! $redis->set($cacheKey, 'running', ['NX', 'EX' => self::RUNNING_CACHE_TTL_SECONDS])) {
            $this->info('preStart skipped because a recent execution exists.');
            return;
        }

        try {
            $this->runPreStartTasks();
        } catch (\Throwable $throwable) {
            // 执行失败时释放缓存，避免阻止运维立即重试。
            $redis->del($cacheKey);
            throw $throwable;
        }

        // 成功后仅保留短时缓存，覆盖 Docker 命令结束到 Hyperf 主进程启动之间的间隔。
        $redis->expire($cacheKey, self::COMPLETED_CACHE_TTL_SECONDS);
    }

    private function runPreStartTasks(): void
    {
        $this->call('migrate', ['--path' => './vendor/bailing/helper/src/migrations']);
        $this->call('migrate');

        // 扫描数据表字段的redis缓存，且删除掉
        redisDelByPattern('database_table_column_type:*');

        // 检测mq的queue、exchange是否以当前服务名开始，避免复制其他代码导致queue相同，引发问题（system.开头的代表系统级）
        if (env('AMQP_USER') && env('AMQP_PASSWORD') && env('APP_NAME')) {
            $consumerExchangeArr = [];
            // Consumer的queue必须以当前服务名开始
            $class = AnnotationCollector::getClassesByAnnotation(Consumer::class);
            if (! empty($class)) {
                foreach ($class as $item) {
                    if (! empty($item->queue) && stripos($item->queue, env('APP_NAME')) !== 0) {
                        stdLog()->error('MQ consumer queue must start with the service name (' . env('APP_NAME') . '): ' . $item->queue);
                        Process::kill((int) file_get_contents(\Hyperf\Config\config('server.settings.pid_file')));
                        break;
                    }
                    $consumerExchangeArr[] = $item->exchange;
                }
            }

            // Producer的exchange必须要以本服务名开始，特别是当本服务的Consumer存在的时候，避免命令为其他服务。
            $class = AnnotationCollector::getClassesByAnnotation(Producer::class);
            if (! empty($class)) {
                foreach ($class as $item) {
                    if (! empty($item->exchange) && stripos($item->exchange, env('APP_NAME')) !== 0 && stripos($item->exchange, 'system') !== 0 && in_array($item->exchange, $consumerExchangeArr)) {
                        stdLog()->error('MQ producer exchange must start with the service name (' . env('APP_NAME') . '): ' . $item->exchange);
                        Process::kill((int) file_get_contents(\Hyperf\Config\config('server.settings.pid_file')));
                        break;
                    }
                }
            }
        }

        // 初始化打开 xxl-job
        stdLog()->info('xxl-job-task init now');
        if (env('XXL_JOB_ENABLE') === true) {
            stdLog()->info('xxl-job is enable');
            $xxlJobTaskHelper = new XxlJobTaskHelper();
            $xxlJobTaskHelper->build();
        }

        // 初始化创建 rabbit-mq vhost
        stdLog()->info('rabbit-mq vhost init now');
        if (env('AMQP_VHOST_AUTO_CREATE') === true && env('AMQP_PORT_ADMIN')) {
            $clientHttp = new Client();
            try {
                $response = $clientHttp->request('PUT', sprintf('http://%s:%s/api/vhosts/%s', env('AMQP_HOST'), env('AMQP_PORT_ADMIN'), env('AMQP_BALING_VHOST', 'bailing')), [
                    'auth' => [env('AMQP_USER'), env('AMQP_PASSWORD')],
                    'content-type' => 'application/json',
                ]);

                $mqResultCode = $response->getStatusCode();
                if ($mqResultCode == 201 || $mqResultCode == 204) {
                    stdLog()->info('rabbit-mq vhost create ok');
                }
            } catch (GuzzleException $e) {
                stdLog()->error('rabbit vhost create error：' . $e->getMessage());
            }
        }

        // 国际化上报
        (new TranslationReportHelper())->build();

        // i18n国际化上报
        (new I18nTranslationReportHelper())->build();

        // webhook服务注册
        stdLog()->info('registerServiceWebhook');
        (new WebhookInvokeHelper())->registerServiceWebhook();

        // webhook服务注册node节点
        stdLog()->info('registerServiceWebhookNode');
        (new WebhookInvokeHelper())->registerServiceWebhookNode();

        // 业务服务可监听该事件执行启动初始化，避免相关代码驻留在主 Worker 进程。
        container()->get(EventDispatcherInterface::class)->dispatch(new PreStart());
    }
}

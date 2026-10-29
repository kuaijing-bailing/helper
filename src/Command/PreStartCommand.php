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
use Bailing\Helper\XxlJobTaskHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Annotation\Producer;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Database\Migrations\Migrator;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\Inject;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Process;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process as ConsoleProcess;

/**
 * 执行启动前迁移及初始化；迁移按文件隔离，失败后禁止启动。
 * 在业务服务根目录执行：php bin/hyperf.php preStart --no-interaction。
 * 更新代码后忽略短时成功缓存：php bin/hyperf.php preStart --force --no-interaction。
 */
#[Command]
class PreStartCommand extends HyperfCommand
{
    /** 服务级启动锁及短时成功缓存的键前缀。 */
    private const CACHE_KEY_PREFIX = 'service_auto:pre_start_command:';

    /** 保留原有运行锁过期时间，单位秒，不额外引入续租协程。 */
    private const RUNNING_CACHE_TTL_SECONDS = 900;

    /** 成功缓存保留时间，单位秒，避免入口脚本与启动监听重复初始化。 */
    private const COMPLETED_CACHE_TTL_SECONDS = 30;

    /** 查询待执行迁移及完成记录，父进程不直接执行迁移。 */
    #[Inject]
    protected Migrator $migrator;

    /** 注册预启动命令。 */
    public function __construct()
    {
        parent::__construct('preStart');
    }

    /** 配置更新代码后强制重新检查的选项。 */
    public function configure(): void
    {
        parent::configure();
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Ignore the recent successful preStart cache.');
    }

    /** 在互斥锁内执行全部任务，保留异常以让命令返回失败。 */
    public function handle(): void
    {
        try {
            $redis = redis();
            $cacheKey = self::CACHE_KEY_PREFIX . strval(env('MICRO_NAME', env('APP_NAME')));
            $token = bin2hex(random_bytes(16));
            // 强制检查只绕过成功缓存，不能把另一进程仍在执行误判为初始化完成。
            $state = $redis->eval(
                "local value = redis.call('get', KEYS[1]); if not value or (value == 'completed' and ARGV[3] == '1') then redis.call('set', KEYS[1], ARGV[1], 'EX', ARGV[2]); return 1; end; if value == 'completed' then return 2; end; return 0;",
                [$cacheKey, $token, self::RUNNING_CACHE_TTL_SECONDS, $this->input->getOption('force') ? '1' : '0'],
                1
            );
            if ($state === 2) {
                $this->info('preStart skipped because a recent execution completed successfully.');
                return;
            }
            if ($state !== 1) {
                throw new \RuntimeException('preStart is already running; startup must wait and retry.');
            }
            try {
                $this->runPreStartTasks();
                // 耗时超过原有 TTL 且无人接管时允许完成；不得覆盖另一进程持有的锁。
                if ($redis->eval(
                    "local value = redis.call('get', KEYS[1]); if not value or value == ARGV[1] then redis.call('set', KEYS[1], 'completed', 'EX', ARGV[2]); return 1; end; return 0;",
                    [$cacheKey, $token, self::COMPLETED_CACHE_TTL_SECONDS],
                    1
                ) !== 1) {
                    throw new \RuntimeException('preStart lost ownership of its startup lock.');
                }
            } finally {
                // 失败释放本次锁以便重试，成功缓存及其他进程的锁不受影响。
                $redis->eval("if redis.call('get', KEYS[1]) == ARGV[1] then return redis.call('del', KEYS[1]); end; return 0;", [$cacheKey, $token], 1);
            }
        } finally {
            // 兼容未自动发送退出通知的 Hyperf；迁移、初始化和锁清理全部结束后再关闭后台协程。
            CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
        }
    }

    /** 按文件调度独立进程；组件先于业务执行，每批保留原来的回滚范围。 */
    protected function runMigrations(string $componentPath, string $applicationPath): void
    {
        $this->migrator->setConnection('default');
        $repository = $this->migrator->getRepository();
        if (! $repository->repositoryExists()) {
            // 不嵌套调用 migrate:install，避免命令结束时提前发送全局退出信号。
            $repository->createRepository();
        }
        $ran = array_fill_keys($repository->getRan(), true);
        foreach ([[$componentPath], [...$this->migrator->paths(), $applicationPath]] as $paths) {
            $batch = $repository->getNextBatchNumber();
            foreach ($this->migrator->getMigrationFiles($paths) as $name => $file) {
                if (isset($ran[$name])) {
                    continue;
                }
                $process = $this->createMigrationProcess([
                    'migrate:isolated', '--database=default', '--path=' . $file, '--batch=' . $batch, '--realpath', '--force', '--no-interaction',
                ]);
                $process->mustRun(static function (string $type, string $buffer) use ($process): void {
                    fwrite($type === ConsoleProcess::ERR ? STDERR : STDOUT, $buffer);
                    // 输出已透传，避免长时间迁移在父进程重复积累日志。
                    $process->clearOutput();
                    $process->clearErrorOutput();
                });
                $ran = array_fill_keys($repository->getRan(), true);
                if (! isset($ran[$name])) {
                    throw new \RuntimeException(sprintf('Migration [%s] exited without recording completion.', $name));
                }
            }
        }
    }

    /**
     * 创建无运行时限的迁移进程，避免耗时迁移被默认超时中断。
     * @param string[] $arguments 命令及独立参数，交由 Process 处理路径转义
     */
    protected function createMigrationProcess(array $arguments): ConsoleProcess
    {
        return new ConsoleProcess([PHP_BINARY, BASE_PATH . '/bin/hyperf.php', ...$arguments], null, null, null, null);
    }

    /** 顺序执行迁移与服务初始化，任一迁移失败时不再执行后续任务。 */
    protected function runPreStartTasks(): void
    {
        $this->runMigrations(dirname(__DIR__) . '/migrations', BASE_PATH . '/migrations');

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
            $amqpVhost = (string) config('amqp.default.vhost', env('AMQP_BALING_VHOST', env('AMQP_VHOST', 'bailing')));
            try {
                $response = $clientHttp->request('PUT', sprintf('http://%s:%s/api/vhosts/%s', env('AMQP_HOST'), env('AMQP_PORT_ADMIN'), rawurlencode($amqpVhost)), [
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

        // 业务服务可监听该事件执行启动初始化，避免相关代码驻留在主 Worker 进程。
        container()->get(EventDispatcherInterface::class)->dispatch(new PreStart());
    }
}

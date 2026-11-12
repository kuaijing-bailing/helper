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
use Hyperf\Coordinator\Coordinator;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Database\Migrations\Migrator;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Process;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process as ConsoleProcess;

/**
 * 执行启动前迁移及初始化；迁移按文件隔离，失败后禁止启动。
 * 在业务服务根目录执行：php bin/hyperf.php preStart。
 * 默认关闭交互并复用 30 秒内的成功结果，避免入口脚本与 start 重复初始化。
 * 手动忽略成功缓存时执行：php bin/hyperf.php preStart --force。
 */
#[Command]
class PreStartCommand extends HyperfCommand
{
    /** 服务级启动锁及短时成功缓存的键前缀。 */
    private const string CACHE_KEY_PREFIX = 'service_auto:pre_start_command:';

    /** 运行锁租期，单位秒，容器异常退出后由 Redis 自动释放。 */
    private const int RUNNING_CACHE_TTL_SECONDS = 30;

    /** 持锁期间的续租间隔，单位秒。 */
    private const int LOCK_RENEW_INTERVAL_SECONDS = 5;

    /** 等待其他实例完成或遗留锁过期时的重试间隔，单位秒。 */
    private const int LOCK_RETRY_INTERVAL_SECONDS = 1;

    /** 成功缓存保留时间，单位秒，避免入口脚本与启动监听重复初始化。 */
    private const int COMPLETED_CACHE_TTL_SECONDS = 30;

    /** 查询待执行迁移及完成记录，父进程不直接执行迁移。 */
    #[Inject]
    protected Migrator $migrator;

    /** 后台续租失败的原因，交回命令所在协程以阻止后续任务。 */
    private ?\Throwable $renewalFailure = null;

    /** 注册预启动命令。 */
    public function __construct()
    {
        parent::__construct('preStart');
    }

    /** 配置需要手动忽略短时成功缓存时使用的选项。 */
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
            if (! $this->acquireStartupLock($redis, $cacheKey, $token)) {
                return;
            }
            $finished = new Coordinator();
            try {
                $this->renewalFailure = null;
                $this->startLockRenewal($redis, $cacheKey, $token, $finished);
                $this->runPreStartTasks();
                $this->checkLockRenewal();
                // 只有仍持锁的进程能发布成功状态，过期或已被接管时必须重试。
                if ($redis->eval(
                    "if redis.call('get', KEYS[1]) == ARGV[1] then redis.call('set', KEYS[1], 'completed', 'EX', ARGV[2]); return 1; end; return 0;",
                    [$cacheKey, $token, self::COMPLETED_CACHE_TTL_SECONDS],
                    1
                ) !== 1) {
                    throw new \RuntimeException('preStart lost ownership of its startup lock.');
                }
            } finally {
                $finished->resume();
                // 失败释放本次锁以便重试，成功缓存及其他进程的锁不受影响。
                $redis->eval("if redis.call('get', KEYS[1]) == ARGV[1] then return redis.call('del', KEYS[1]); end; return 0;", [$cacheKey, $token], 1);
            }
        } finally {
            // 兼容未自动发送退出通知的 Hyperf；迁移、初始化和锁清理全部结束后再关闭后台协程。
            CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
        }
    }

    /** 默认不等待交互输入，保留成功缓存以兼容先 preStart 再 start 的启动脚本。 */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $input->setInteractive(false);
    }

    /** 按文件调度独立进程；组件先于业务执行，每批保留原来的回滚范围。 */
    protected function runMigrations(string $componentPath, string $applicationPath): void
    {
        if (! is_dir($componentPath) || ! is_readable($componentPath)) {
            throw new \RuntimeException(sprintf('Component migration directory is missing or unreadable: %s', $componentPath));
        }
        $componentFiles = $this->migrator->getMigrationFiles([$componentPath]);
        if ($componentFiles === []) {
            throw new \RuntimeException(sprintf('No component migration files found in: %s', $componentPath));
        }
        $this->info(sprintf('preStart component migration path: %s', $componentPath));
        $this->migrator->setConnection('default');
        $repository = $this->migrator->getRepository();
        if (! $repository->repositoryExists()) {
            // 不嵌套调用 migrate:install，避免命令结束时提前发送全局退出信号。
            $repository->createRepository();
        }
        $ran = array_fill_keys($repository->getRan(), true);
        foreach (['component' => [$componentPath], 'application' => [...$this->migrator->paths(), $applicationPath]] as $scope => $paths) {
            $files = $scope === 'component' ? $componentFiles : $this->migrator->getMigrationFiles($paths);
            $pending = array_diff_key($files, $ran);
            $this->info(sprintf('preStart %s migrations: found=%d, pending=%d.', $scope, count($files), count($pending)));
            $batch = $repository->getNextBatchNumber();
            foreach ($files as $name => $file) {
                if (isset($ran[$name])) {
                    continue;
                }
                $this->checkLockRenewal();
                $process = $this->createMigrationProcess([
                    'migrate:isolated', '--database=default', '--path=' . $file, '--batch=' . $batch, '--realpath', '--force', '--no-interaction',
                ]);
                $this->waitForMigration($process);
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
        // 沿用原 migrate --path 的组件安装路径，避免 __DIR__ 被代理缓存固化为构建目录。
        $this->runMigrations(BASE_PATH . '/vendor/bailing/helper/src/migrations', BASE_PATH . '/migrations');
        $this->checkLockRenewal();

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
        $this->checkLockRenewal();
        stdLog()->info('xxl-job-task init now');
        if (env('XXL_JOB_ENABLE') === true) {
            stdLog()->info('xxl-job is enable');
            $xxlJobTaskHelper = new XxlJobTaskHelper();
            $xxlJobTaskHelper->build();
        }

        // 初始化创建 rabbit-mq vhost
        $this->checkLockRenewal();
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
        $this->checkLockRenewal();
        (new TranslationReportHelper())->build();

        // i18n国际化上报
        $this->checkLockRenewal();
        (new I18nTranslationReportHelper())->build();

        // 业务服务可监听该事件执行启动初始化，避免相关代码驻留在主 Worker 进程。
        $this->checkLockRenewal();
        container()->get(EventDispatcherInterface::class)->dispatch(new PreStart());
    }

    /**
     * 等待服务级锁释放或过期后原子获取，即使强制执行也要等待当前持有者。
     * @return bool 已取得锁时返回 true，命中成功缓存时返回 false
     */
    private function acquireStartupLock(Redis $redis, string $cacheKey, string $token): bool
    {
        $arguments = [$cacheKey, $token, self::RUNNING_CACHE_TTL_SECONDS, $this->input->getOption('force') ? '1' : '0'];
        $waiting = false;
        while (true) {
            $state = $redis->eval(<<<'LUA'
                local value = redis.call('get', KEYS[1])
                if not value or (value == 'completed' and ARGV[3] == '1') then
                    redis.call('set', KEYS[1], ARGV[1], 'EX', ARGV[2])
                    return 1
                end
                if value == 'completed' then return 2 end
                return 0
                LUA, $arguments, 1);
            if ($state === 1) {
                return true;
            }
            if ($state === 2) {
                $this->info('preStart skipped because a recent execution completed successfully.');
                return false;
            }
            if ($state !== 0) {
                throw new \RuntimeException('Unable to acquire the preStart lock in Redis.');
            }
            if (! $waiting) {
                $this->info('Waiting for the existing preStart lock to be released or expire.');
                $waiting = true;
            }
            Coroutine::sleep(self::LOCK_RETRY_INTERVAL_SECONDS);
        }
    }

    /** 透传迁移输出，空闲时让出执行权以便续租；失锁时终止子进程。 */
    private function waitForMigration(ConsoleProcess $process): void
    {
        $process->start();
        try {
            foreach ($process->getIterator(ConsoleProcess::ITER_NON_BLOCKING) as $type => $buffer) {
                $this->checkLockRenewal();
                if ($buffer === '') {
                    Coroutine::sleep(0.1);
                } else {
                    fwrite($type === ConsoleProcess::ERR ? STDERR : STDOUT, $buffer);
                }
            }
            $this->checkLockRenewal();
            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
        } finally {
            if ($process->isRunning()) {
                $process->stop(0);
            }
        }
    }

    /** 在任务运行期间仅为本次令牌续租，将错误交给正在运行任务的协程。 */
    private function startLockRenewal(Redis $redis, string $cacheKey, string $token, Coordinator $finished): void
    {
        $id = Coroutine::create(function () use ($redis, $cacheKey, $token, $finished): void {
            try {
                while (! $finished->yield(self::LOCK_RENEW_INTERVAL_SECONDS)) {
                    if ($redis->eval(
                        "if redis.call('get', KEYS[1]) == ARGV[1] then return redis.call('expire', KEYS[1], ARGV[2]); end; return 0;",
                        [$cacheKey, $token, self::RUNNING_CACHE_TTL_SECONDS],
                        1
                    ) !== 1) {
                        throw new \RuntimeException('preStart could not renew its startup lock.');
                    }
                }
            } catch (\Throwable $exception) {
                $this->renewalFailure = $exception;
            }
        });
        if ($id < 1) {
            throw new \RuntimeException('Could not start the preStart lock renewal coroutine.');
        }
    }

    /** 将续租协程的异常抛回执行任务的协程。 */
    private function checkLockRenewal(): void
    {
        if ($this->renewalFailure !== null) {
            throw $this->renewalFailure;
        }
    }
}

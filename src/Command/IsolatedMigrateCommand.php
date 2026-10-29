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

use Bailing\Helper\MigrationFileMigrator;
use Hyperf\Command\Annotation\Command;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Database\Commands\Migrations\MigrateCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * 在独立进程执行一个迁移文件，执行和记录结束后统一清理常驻协程。
 * 在服务根目录执行：php bin/hyperf.php migrate:isolated --path=migrations/2026_01_01_000000_example.php --force --no-interaction。
 * 示例文件名需替换为实际待执行文件；批量启动迁移应使用 php bin/hyperf.php preStart --force --no-interaction。
 */
#[Command]
class IsolatedMigrateCommand extends MigrateCommand
{
    /** 注入框架迁移器，复用原有迁移事务和完成记录机制。 */
    public function __construct(
        /** 在父进程指定的批次内执行文件，保持既有回滚范围。 */
        private MigrationFileMigrator $fileMigrator
    ) {
        parent::__construct($fileMigrator);
        $this->setName('migrate:isolated');
    }

    /** 单文件执行结束后发送退出通知，兼容尚未自动清理的 Hyperf 版本。 */
    public function handle(): int
    {
        try {
            $paths = $this->getMigrationPaths();
            if (count($paths) !== 1 || ! str_ends_with($paths[0], '.php') || ! is_file($paths[0])) {
                throw new \InvalidArgumentException('migrate:isolated requires one existing migration file via --path.');
            }
            $batch = $this->input->getOption('batch');
            if ($batch !== null && filter_var($batch, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                throw new \InvalidArgumentException('Migration batch must be a positive integer.');
            }
            $this->fileMigrator->setBatch($batch === null ? null : (int) $batch);
            return parent::handle();
        } finally {
            // 只能在整个迁移及记录写入结束后清理；此进程不再执行后续迁移或初始化任务。
            CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
        }
    }

    /** 补充内部调度批次选项，其余选项保持框架行为。 */
    protected function getOptions(): array
    {
        return [...parent::getOptions(), ['batch', null, InputOption::VALUE_REQUIRED, 'Batch number reserved by the migration scheduler']];
    }

    /** 直接创建迁移记录表，避免嵌套 migrate:install 提前关闭退出协调器。 */
    protected function prepareDatabase(): void
    {
        $this->migrator->setConnection($this->input->getOption('database') ?? 'default');
        $repository = $this->migrator->getRepository();
        if (! $repository->repositoryExists()) {
            $repository->createRepository();
        }
    }
}

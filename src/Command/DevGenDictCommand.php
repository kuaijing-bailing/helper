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

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Stringable\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class DevGenDictCommand extends HyperfCommand
{
    public function __construct()
    {
        parent::__construct('dev:genDict');
    }

    public function configure()
    {
        parent::configure();
        $this->addArgument('className', InputArgument::REQUIRED, '类名');
        $this->addOption('dir', 'd', InputOption::VALUE_REQUIRED, '欲生成所在的目录名（例：输入Org会生成在app/Constants/I18n/Org）');
        $this->setDescription('生成dict的代码（name、type、menuAlias自行补全）');
    }

    public function handle()
    {
        $this->line('开始生成!', 'info');

        $className = $this->input->getArgument('className');
        $className = ucfirst($className);
        if (! str_ends_with($className, 'Dict')) {
            $className .= 'Dict';
        }

        $dir = Str::ucfirst(trim(strval($this->input->getOption('dir'))));

        $stub = file_get_contents(__DIR__ . '/stubs/DevDict.stub');
        $stub = str_replace('%DIR_NAME%', $dir, $stub);
        $stub = str_replace('%CLASS_NAME%', $className, $stub);
        $stub = str_replace('%DIR_CLASS_NAME%', Str::snake($dir) . '_' . Str::snake($className), $stub);

        if (! file_exists(BASE_PATH . '/app/Constants/Dict/' . $dir)) {
            mkdir(BASE_PATH . '/app/Constants/Dict/' . $dir, 0777, true);
        }

        $fileName = BASE_PATH . '/app/Constants/Dict/' . $dir . '/' . $className . '.php';
        file_put_contents($fileName, $stub);

        $this->line($fileName . '已生成，请自行补充里面的内容', 'info');

        CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
    }
}

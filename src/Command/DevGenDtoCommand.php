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

use Bailing\Helper\Intl\I18nHelper;
use Bailing\Helper\TranslationHelper;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Stringable\Str;
use Symfony\Component\Console\Input\InputArgument;
use Vtiful\Kernel\Excel;

#[Command]
class DevGenDtoCommand extends HyperfCommand
{
    public function __construct()
    {
        parent::__construct('dev:genDto');
    }

    public function configure()
    {
        parent::configure();
        $this->addArgument('className', InputArgument::REQUIRED, '类名');
        $this->setDescription('生成dto的代码');
    }

    public function handle()
    {
        $this->line('开始生成!', 'info');

        $className = $this->input->getArgument('className');
        $className = ucfirst($className);
        if (! str_ends_with($className, 'Dto')) {
            $className .= 'Dto';
        }

        $fileName = BASE_PATH . '/runtime/i18n.xlsx';
        if (! file_exists($fileName)) {
            // 查找一次任意xlsx
            $files = glob(BASE_PATH . '/runtime/*.xlsx');
            if (! empty($files)) {
                if (count($files) > 1) {
                    $this->line('runtime文件夹下面有多个xlsx文件，请检查。或者重命名为i18n.xlsx', 'error');
                    return;
                }
                $fileName = $files[0];
            } else {
                $this->line('runtime/i18n.xlsx不存在，请检查', 'error');
                return;
            }
        }

        $config = ['path' => dirname($fileName)];
        $excel = new Excel($config);
        $data = $excel->openFile(basename($fileName))
            ->openSheet()
            ->getSheetData();
        if (empty($data[0][0])) {
            $this->line('runtime/i18n.xlsx文件内容异常，请检查', 'error');
            return;
        }

        $stub = file_get_contents(__DIR__ . '/stubs/DevDto.stub');
        $stub = str_replace('%CLASS_NAME%', $className, $stub);

        $i18nCode = '';
        $nowIndex = 0;
        foreach ($data[0] as $key => $value) {
            if (empty($value)) {
                continue;
            }
            $i18nTxt = I18nHelper::translateArr($value, true);

            $i18nCode .= "    #[ExcelProperty(value: '" . $value . "', index: " . $nowIndex . ", demo: '" . (! empty($data[1][$key]) ? addslashes((string) $data[1][$key]) : '') . "', i18nValue: " . $i18nTxt . ", width: 30, height: 25, align: 'center', required: false, headHeight: 30)]" . PHP_EOL;
            $i18nCode .= '    public string $' . Str::snake(TranslationHelper::translate($value, 'zh-CN', 'en')) . ';' . PHP_EOL;
            ++$nowIndex;
        }

        $stub = str_replace('%EXCEL_PROPERTY%', $i18nCode, $stub);

        if (! file_exists(BASE_PATH . '/app/Dto')) {
            mkdir(BASE_PATH . '/app/Dto', 0777, true);
        }

        $fileName = BASE_PATH . '/app/Dto/' . $className . '.php';
        file_put_contents($fileName, $stub);

        $this->line($fileName . '已生成，请自行补充里面的内容', 'info');

        CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
    }
}

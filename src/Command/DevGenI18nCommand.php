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
use Bailing\Helper\StrHelper;
use Hyperf\Codec\Json;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Stringable\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class DevGenI18nCommand extends HyperfCommand
{
    public function __construct()
    {
        parent::__construct('dev:genI18n');
    }

    public function configure()
    {
        parent::configure();
        $this->addArgument('className', InputArgument::REQUIRED, '类名');
        $this->addOption('dir', 'd', InputOption::VALUE_REQUIRED, '欲生成所在的目录名（例：输入Org会生成在app/Constants/I18n/Org）');
        $this->addOption('field', 'f', InputOption::VALUE_OPTIONAL, '数组中的字段值（默认为title）');
        $this->addOption('key', 'k', InputOption::VALUE_OPTIONAL, '数组中的字段键（默认为key）');
        $this->setDescription('生成i18n的代码（groupCode、info自行补全）');
    }

    public function handle()
    {
        $this->line('开始生成!', 'info');

        $className = $this->input->getArgument('className');
        $className = ucfirst($className);
        if (! str_ends_with($className, 'I18n')) {
            $className .= 'I18n';
        }

        $field = $this->input->getOption('field', '');
        $key = $this->input->getOption('key', '');
        $dir = Str::ucfirst(trim(strval($this->input->getOption('dir'))));

        if (! file_exists(BASE_PATH . '/runtime/i18n.json')) {
            touch(BASE_PATH . '/runtime/i18n.json');
            $this->line('runtime/i18n.json文件不存在，已经自动创建，请填充内容', 'error');
            return;
        }

        $i18nArr = [];
        $i18nJson = StrHelper::mb_trim(file_get_contents(BASE_PATH . '/runtime/i18n.json'));
        if (! empty($i18nJson)) {
            // 数组
            if (str_contains($i18nJson, ' => ')) {
                $this->i18nArray($i18nJson, $dir, $className, $field ?: 'name');
                return;
            }

            $i18nArr = Json::decode($i18nJson);

            if (empty($i18nArr[0])) {
                $i18nArr = [
                    [
                        'title' => 'demo',
                        'key' => 'demo',
                    ],
                ];
            }

            if (empty($field)) {
                if (empty($i18nArr[0]['title'])) {
                    $this->line('runtime/i18n.json文件的 title 键不存在，请传递 field 参数，申明哪个字段需要翻译', 'error');
                    return;
                }
                $field = 'title';
            }
            if (empty($key)) {
                if (empty($i18nArr[0]['key'])) {
                    $this->line('runtime/i18n.json文件的 key 键不存在，请传递 key 参数，申明哪个字段作为键', 'error');
                    return;
                }
                $key = 'key';
            }
        }

        $stub = file_get_contents(__DIR__ . '/stubs/DevI18n.stub');
        $stub = str_replace('%DIR_NAME%', $dir, $stub);
        $stub = str_replace('%CLASS_NAME%', $className, $stub);

        $i18nCode = '';
        foreach ($i18nArr as $item) {
            $chinese = $item[$field];
            $i18nTxt = '';
            if ($chinese != 'demo') {
                $i18nTxt = I18nHelper::translateArr($chinese, true);
            }

            $enumKey = strtoupper($item[$key]);

            $i18nCode .= "    #[EnumI18n(txt: '" . $chinese . "'" . (! empty($i18nTxt) ? ', i18nTxt: ' . $i18nTxt : '') . ')]' . PHP_EOL . '    case ' . $enumKey . " = '" . $item[$key] . "';" . PHP_EOL;
        }

        $stub = str_replace('%I18N_CODE%', $i18nCode, $stub);

        if (! file_exists(BASE_PATH . '/app/Constants/I18n/' . $dir)) {
            mkdir(BASE_PATH . '/app/Constants/I18n/' . $dir, 0777, true);
        }

        $fileName = BASE_PATH . '/app/Constants/I18n/' . $dir . '/' . $className . '.php';
        file_put_contents($fileName, $stub);

        $this->line($fileName . '已生成，请自行补充里面的内容', 'info');

        CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
    }

    public function i18nArray(string $i18nJson, string $dir, string $className, string $field = 'name')
    {
        preg_match_all('/\'' . $field . '\' => \'(.*?)\',/', $i18nJson, $matches);

        if (empty($matches[1])) {
            $this->line('runtime/i18n.json文件不是标准的数组，解析失败', 'error');
            return;
        }

        $stub = file_get_contents(__DIR__ . '/stubs/DevI18n.stub');
        $stub = str_replace('%DIR_NAME%', $dir, $stub);
        $stub = str_replace('%CLASS_NAME%', $className, $stub);

        $i18nCode = '';
        foreach ($matches[1] as $key => $item) {
            $i18nTxt = I18nHelper::translateArr($item, true);

            $itemKey = 'ITEM_' . $key;
            $i18nCode .= "    #[EnumI18n(txt: '" . $item . "', i18nTxt: " . $i18nTxt . ')]' . PHP_EOL . '    case ' . $itemKey . " = '" . $key . "';" . PHP_EOL;

            $i18nJson = str_replace($matches[0][$key], $matches[0][$key] . PHP_EOL . '\'i18n_' . $field . '\' => ' . $className . '::' . $itemKey . '->genI18nTxt(),', $i18nJson);
        }

        $stub = str_replace('%I18N_CODE%', $i18nCode, $stub);

        if (! file_exists(BASE_PATH . '/app/Constants/I18n/' . $dir)) {
            mkdir(BASE_PATH . '/app/Constants/I18n/' . $dir, 0777, true);
        }

        $fileName = BASE_PATH . '/app/Constants/I18n/' . $dir . '/' . $className . '.php';
        file_put_contents($fileName, $stub);

        file_put_contents(BASE_PATH . '/runtime/i18n.json', $i18nJson);

        $this->line($fileName . '已生成，请自行补充里面的内容。runtime/i18n.json的内容也换成了新的，请自行替换回去', 'info');

        CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
    }
}

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
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;
use Hyperf\Stringable\Str;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class GenCRUDCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('gen:crud');
    }

    public function configure()
    {
        parent::configure();
        $this->addArgument('table', InputArgument::REQUIRED, '数据表名');
        $this->addOption('dir', 'd', InputOption::VALUE_OPTIONAL, '欲生成控制器所在的目录名（例：输入Org会生成在app/Controller/Org）');
        $this->setDescription('生成CRUD代码');
    }

    public function handle()
    {
        $this->line('开始生成!', 'info');

        $dir = Str::ucfirst(trim(strval($this->input->getOption('dir'))));
        if (empty($dir)) {
            $dir = 'Org';
        } else {
            $dirArr = explode('/', $dir);
            foreach ($dirArr as &$item) {
                $item = Str::studly($item);
            }
            $dir = implode('/', $dirArr);
        }

        // 引入model
        $table = $this->input->getArgument('table');
        $model = Str::ucfirst(Str::camel(trim($table)));
        $tmp = 'App\Model\\' . $model;
        if (! class_exists($tmp)) {
            // 尝试自动构建Model
            passthru('cd ' . BASE_PATH . ' && php bin/hyperf.php gen:model ' . $table);

            // 尝试自动构建trimFields
            $columnsTypeArr = array_column(Schema::getColumnTypeListing($table), null, 'column_name');
            $trimFields = [];
            $attributesArr = [];
            foreach ($columnsTypeArr as $item) {
                if (! empty($item['data_type']) && in_array($item['data_type'], ['varchar', 'char'])) {
                    $trimFields[] = $item['column_name'];
                }
                if (! empty($item['column_comment'])) {
                    $attributesArr[$item['column_name']] = $item['column_comment'];
                }
            }
            $modelFile = $this->makeDirectory(BASE_PATH . '/app/Model/') . $model . '.php';

            // 新增使用到的类（软删除、修改事件监听）
            $modelFileContent = file_get_contents($modelFile);
            $modelFileContent = str_replace('namespace App\Model;', 'namespace App\Model;' . PHP_EOL . 'use Hyperf\Database\Model\SoftDeletes;' . PHP_EOL . 'use Bailing\Trait\DbModifyLog;', $modelFileContent);

            $stub = file_get_contents(__DIR__ . '/stubs/TrimFields.stub');
            $stub = $this->replaceTrimFields($stub, $trimFields);
            $stub = $this->replaceAttributes($stub, $attributesArr);
            $trimFieldsStr = str_replace(PHP_EOL . '}' . PHP_EOL, $stub . PHP_EOL . '}' . PHP_EOL, $modelFileContent);

            file_put_contents($modelFile, $trimFieldsStr);
            // trimFields完成

            // 尝试重新执行一次cmd，为了重新加载生成的model
            passthru('cd ' . BASE_PATH . ' && php bin/hyperf.php gen:crud ' . $table . ' --dir=' . $dir);
            return;
        }
        $class = new $tmp();
        if (! method_exists($class, 'trimFields')) {
            $this->line($model . ' Model 的 trimFields 方法不存在，请增加', 'error');
            return;
        }

        // 生成Controller
        $stub = file_get_contents(__DIR__ . '/stubs/Controller.stub');
        $stub = $this->replaceClass($stub, $model);
        $stub = $this->replaceDir($stub, $dir);
        $stub = $this->replaceRoute($stub, $dir, $model);
        $stub = $this->replaceControllerFields($stub, $table);
        $controllerDir = $this->makeDirectory(BASE_PATH . '/app/Controller/' . $dir);
        file_put_contents($controllerDir . '/' . $model . 'Controller.php', $stub);

        // 生成Request
        $stub = file_get_contents(__DIR__ . '/stubs/Request.stub');
        $stub = $this->replaceClass($stub, $model);
        $stub = $this->replaceTrimFields($stub, $class::trimFields());
        $stub = $this->replaceRequestFields($stub, $table, $class::trimFields());
        $requestDir = $this->makeDirectory(BASE_PATH . '/app/Request');
        file_put_contents($requestDir . '/' . $model . 'Request.php', $stub);
    }

    protected function makeDirectory(string $path): string
    {
        if (! is_dir($path)) {
            mkdir($path, 0777, true);
        }

        return $path;
    }

    protected function replaceClass(string $stub, string $name): string
    {
        return str_replace('%CLASS%', $name, $stub);
    }

    protected function replaceDir(string $stub, string $dir): string
    {
        $dir = str_replace('/', '\\', $dir);
        return str_replace('%DIR%', $dir, $stub);
    }

    protected function replaceRoute(string $stub, string $dir, string $model): string
    {
        $route = '/' . Str::snake(str_replace('/', '', $dir), '/') . '/' . Str::snake($model, '/');
        return str_replace('%ROUTER%', $route, $stub);
    }

    protected function replaceTrimFields(string $stub, array $fields): string
    {
        $str = implode("', '", $fields);
        $str = "'" . $str . "'";
        return str_replace('%TRIM_FIELDS%', $str, $stub);
    }

    protected function replaceAttributes(string $stub, array $attributes): string
    {
        return str_replace('%ATTRIBUTES%', var_export($attributes, true), $stub);
    }

    protected function replaceControllerFields(string $stub, string $table): string
    {
        $columnsDefaultArr = Db::table('INFORMATION_SCHEMA.columns')->where(['TABLE_NAME' => $table])->orderBy('ORDINAL_POSITION')->get(['COLUMN_NAME', 'COLUMN_DEFAULT', 'IS_NULLABLE', 'DATA_TYPE'])->toArray();
        if ($columnsDefaultArr) {
            // 控制器的表单
            $addFields = '';
            foreach ($columnsDefaultArr as $item) {
                if (! in_array($item->COLUMN_NAME, ['id', 'created_at', 'updated_at', 'deleted_at', 'org_id'])) {
                    if ($item->COLUMN_DEFAULT === null && $item->IS_NULLABLE === 'NO') {
                        $addFields .= '$model->' . $item->COLUMN_NAME . ' = ';
                    } else {
                        $addFields .= 'isset($post[\'' . $item->COLUMN_NAME . '\']) && $model->' . $item->COLUMN_NAME . ' = ';
                    }
                    if (in_array(strtolower($item->DATA_TYPE), ['int', 'smallint', 'mediumint', 'tinyint', 'bigint'])) {
                        $addFields .= '(int) $post[\'' . $item->COLUMN_NAME . '\'];';
                    } elseif (in_array(strtolower($item->DATA_TYPE), ['varchar', 'text', 'date', 'datetime', 'timestamp', 'mediumtext', 'longtext', 'char'])) {
                        $addFields .= '(string) $post[\'' . $item->COLUMN_NAME . '\'];';
                    } elseif (in_array(strtolower($item->DATA_TYPE), ['json'])) {
                        $addFields .= '(array) $post[\'' . $item->COLUMN_NAME . '\'];';
                    } elseif (in_array(strtolower($item->DATA_TYPE), ['double', 'float', 'decimal'])) {
                        $addFields .= '(float) $post[\'' . $item->COLUMN_NAME . '\'];';
                    } else {
                        $addFields .= '$post[\'' . $item->COLUMN_NAME . '\'];';
                    }
                    $addFields .= PHP_EOL . '        ';
                }
            }
            $stub = str_replace('%ADD_FIELDS%', trim($addFields), $stub);
        }
        return $stub;
    }

    protected function replaceRequestFields(string $stub, string $table, array $fields): string
    {
        $columnsDefaultArr = Db::table('INFORMATION_SCHEMA.columns')->where(['TABLE_NAME' => $table])->orderBy('ORDINAL_POSITION')->get(['COLUMN_NAME', 'COLUMN_DEFAULT', 'IS_NULLABLE'])->toArray();
        $columnsTypeArr = array_column(Schema::getColumnTypeListing($table), null, 'column_name');
        if ($columnsDefaultArr) {
            $requestFields = '';
            foreach ($columnsDefaultArr as $item) {
                if (! in_array($item->COLUMN_NAME, ['id', 'org_id', 'created_at', 'updated_at', 'deleted_at', 'created_uid', 'updated_uid', 'created_name', 'updated_name'])) {
                    if (in_array($item->COLUMN_NAME, $fields)) {
                        $length = (str_replace([$columnsTypeArr[$item->COLUMN_NAME]['data_type'], '(', ')'], '', $columnsTypeArr[$item->COLUMN_NAME]['column_type']));
                        $requestFields .= "'" . $item->COLUMN_NAME . "' => '" . ($item->COLUMN_DEFAULT === null && $item->IS_NULLABLE === 'NO' ? 'required|' : '') . 'max:' . $length . "'," . PHP_EOL . '            ';
                    } elseif (in_array($columnsTypeArr[$item->COLUMN_NAME]['data_type'], ['int', 'smallint', 'mediumint'])) {
                        $requestFields .= "'" . $item->COLUMN_NAME . "' => '" . ($item->COLUMN_DEFAULT === null && $item->IS_NULLABLE === 'NO' ? 'required|' : '') . "integer'," . PHP_EOL . '            ';
                    } elseif (in_array($columnsTypeArr[$item->COLUMN_NAME]['data_type'], ['tinyint'])) {
                        $requestFields .= "'" . $item->COLUMN_NAME . "' => '" . ($item->COLUMN_DEFAULT === null && $item->IS_NULLABLE === 'NO' ? 'required|' : '') . "integer|lte:128'," . PHP_EOL . '            ';
                    } elseif (in_array($columnsTypeArr[$item->COLUMN_NAME]['data_type'], ['timestamp', 'datetime', 'date'])) {
                        $requestFields .= "'" . $item->COLUMN_NAME . "' => '" . ($item->COLUMN_DEFAULT === null && $item->IS_NULLABLE === 'NO' ? 'required|' : '') . "date'," . PHP_EOL . '            ';
                    } elseif (in_array($columnsTypeArr[$item->COLUMN_NAME]['data_type'], ['json'])) {
                        $requestFields .= "'" . $item->COLUMN_NAME . "' => " . ($item->COLUMN_DEFAULT === null && $item->IS_NULLABLE === 'NO' ? 'required|' : '') . "'array'," . PHP_EOL . '            ';
                    }
                }
            }
            $stub = str_replace('%REQUEST_RULES%', trim($requestFields), $stub);

            // 替换验证字段
            $str = implode("', '", arrayColumnUnique($columnsDefaultArr, 'COLUMN_NAME', ['id', 'org_id', 'created_at', 'updated_at', 'deleted_at', 'created_uid', 'updated_uid', 'created_name', 'updated_name']));
            $str = "'" . $str . "'";
            $stub = str_replace('%REQUEST_FIELDS%', $str, $stub);
        }
        return $stub;
    }
}

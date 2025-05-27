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

use Bailing\Annotation\DictType;
use Bailing\Annotation\EnumCodePrefix;
use Bailing\Annotation\EnumI18nGroup;
use Bailing\Annotation\LinkLibraryPermission;
use Bailing\Annotation\OrgPermission;
use Bailing\Helper\Intl\I18nHelper;
use Bailing\Helper\OrgPermissionHelper;
use Bailing\Office\Annotation\ExcelData;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Di\Annotation\AnnotationCollector;
use ReflectionClass;
use ReflectionEnumBackedCase;

#[Command]
class DevI18nCommand extends HyperfCommand
{
    public function __construct()
    {
        parent::__construct('dev:i18n');
    }

    public function configure()
    {
        parent::configure();
    }

    public function handle()
    {
        $this->line('开始生成机构菜单!', 'info');
        $this->orgPermission();

        $this->line('开始生成链接库!', 'info');
        $this->linkLibraryPermission();

        $this->line('开始生成字典项!', 'info');
        $this->dictI18n();

        $this->line('开始生成Code!', 'info');
        $this->codeI18n();

        $this->line('开始生成I18n!', 'info');
        $this->i18n();

        $this->line('开始生成Dto!', 'info');
        $this->dtoI18n();

        system('rm -rf ' . BASE_PATH . '/runtime');

        CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
    }

    protected function orgPermission()
    {
        $class = AnnotationCollector::getClassesByAnnotation(OrgPermission::class);

        // 特殊处理curd按钮权限
        $orgPermissionHelper = new OrgPermissionHelper();
        $actionAliasList = $orgPermissionHelper->getActionAlias();

        foreach ($class as $key => $value) {
            $tmp = (array) $value;

            // 得到文件名和文件内容
            $reflection = new ReflectionClass($key);
            $fileName = $reflection->getFileName();
            if (str_contains($fileName, 'runtime/container/proxy')) {
                $file = basename($fileName);
                $fileName = str_replace('_', '/', $file);
                $fileName = str_replace('App/', 'app/', $fileName);
                $fileName = str_replace('.proxy.php', '.php', BASE_PATH . '/' . $fileName);
                if (! file_exists($fileName)) {
                    $this->line('文件(' . $file . ')不存在: ' . $fileName, 'error');
                    continue;
                }
            }
            $fileContent = file_get_contents($fileName);

            // 自动补全原文件的名称
            if (! empty($tmp['module']) && empty($tmp['i18nName'])) {
                $moduleNameArr = explode(':', $tmp['module']);
                $name = end($moduleNameArr);

                $matchFileContent = "/#\\[OrgPermission\\(module: '" . $tmp['module'] . "',.*\\]/";
                preg_match($matchFileContent, $fileContent, $matchResult);

                if (! empty($matchResult[0]) && ! str_contains($matchResult[0], 'i18nName')) {
                    $i18nTxt = I18nHelper::translateArr($name, true);

                    $matchContent = "#[OrgPermission(module: '" . $tmp['module'] . "',";
                    $fileSubContent = str_replace($matchContent, $matchContent . ' i18nName: ' . $i18nTxt . ',', $matchResult[0]);

                    $fileContent = str_replace($matchResult[0], $fileSubContent, $fileContent);

                    file_put_contents($fileName, $fileContent);
                }
            }

            // 自动补全action操作的名字
            if (! empty($tmp['action']) && in_array($tmp['action'], $actionAliasList)) {
                $tmp['action'] = 'curd:' . ($orgPermissionHelper->getActionI18nNames($tmp['action'])['value'] ?? '') . '-' . $tmp['action'];
            }

            // 自动补全原文件的操作名称
            if (! empty($tmp['module']) && ! empty($tmp['action']) && ! str_starts_with($tmp['action'], 'curd') && empty($tmp['i18nActionName'])) {
                $actionNameArr = explode('-', explode(':', $tmp['action'])[1]);
                $name = $actionNameArr[0];
                if (in_array($name, ['查看', '新增', '删除', '编辑', '导出', '排序', '字典设置', '审批流设置', '变更状态'])) {
                    $this->line('org菜单中 ' . $tmp['module'] . ' 的 ' . $tmp['action'] . ' 书写格式错误，增删改查应该属于curd', 'error');
                    exit;
                }

                $matchFileContent = "/#\\[OrgPermission\\(module: '" . $tmp['module'] . "', action: '" . $tmp['action'] . "'.*\\]/";
                preg_match($matchFileContent, $fileContent, $matchResult);

                if (! empty($matchResult[0]) && ! str_contains($matchResult[0], 'i18nActionName')) {
                    $i18nTxt = I18nHelper::translateArr($name, true);
                    $matchContent = "#[OrgPermission(module: '" . $tmp['module'] . "', action: '" . $tmp['action'] . "'";
                    $fileSubContent = str_replace($matchContent, $matchContent . ', i18nActionName: ' . $i18nTxt, $matchResult[0]);

                    $fileContent = str_replace($matchResult[0], $fileSubContent, $fileContent);

                    file_put_contents($fileName, $fileContent);
                }
            }
        }

        //得到类方法的所有注解
        $methods = AnnotationCollector::getMethodsByAnnotation(OrgPermission::class);

        foreach ($methods as $value) {
            $annotation = (array) $value['annotation'];

            // 得到文件名和文件内容
            $reflection = new ReflectionClass($value['class']);
            $fileName = $reflection->getFileName();
            if (str_contains($fileName, 'runtime/container/proxy')) {
                $file = basename($fileName);
                $fileName = str_replace('_', '/', $file);
                $fileName = str_replace('App/', 'app/', $fileName);
                $fileName = str_replace('.proxy.php', '.php', BASE_PATH . '/' . $fileName);
                if (! file_exists($fileName)) {
                    $this->line('文件(' . $file . ')不存在: ' . $fileName, 'error');
                    continue;
                }
            }
            $fileContent = file_get_contents($fileName);

            // 自动补全原文件
            if (! empty($annotation['module']) && ! empty($annotation['alias']) && empty($annotation['i18nName'])) {
                $moduleNameArr = explode(':', $annotation['module']);
                $name = end($moduleNameArr);

                $matchFileContent = "/#\\[OrgPermission\\(module: '" . str_replace('/', '\/', $annotation['module']) . "',.*\\]/";
                preg_match($matchFileContent, $fileContent, $matchResult);

                if (! empty($matchResult[0]) && ! str_contains($matchResult[0], 'i18nName')) {
                    $i18nTxt = I18nHelper::translateArr($name, true);

                    $matchContent = "#[OrgPermission(module: '" . $annotation['module'] . "',";
                    $fileSubContent = str_replace($matchContent, $matchContent . ' i18nName: ' . $i18nTxt . ',', $matchResult[0]);

                    $fileContent = str_replace($matchResult[0], $fileSubContent, $fileContent);

                    file_put_contents($fileName, $fileContent);
                }
            }

            // 自动补全action操作的名字
            if (! empty($annotation['action']) && in_array($annotation['action'], $actionAliasList)) {
                $annotation['action'] = 'curd:' . ($orgPermissionHelper->getActionI18nNames($annotation['action'])['value'] ?? '') . '-' . $annotation['action'];
            }

            // 自动补全原文件的操作名称
            if (! empty($annotation['module']) && ! empty($annotation['action']) && ! str_starts_with($annotation['action'], 'curd') && empty($annotation['i18nActionName'])) {
                $actionNameArr = explode('-', explode(':', $annotation['action'])[1]);
                $name = $actionNameArr[0];
                if (in_array($name, ['查看', '新增', '删除', '编辑'])) {
                    $this->line('org菜单中 ' . $annotation['module'] . ' 的 ' . $annotation['action'] . ' 书写格式错误，增删改查应该属于curd', 'error');
                    exit;
                }

                $matchFileContent = "/#\\[OrgPermission\\(module: '" . str_replace('/', '\/', $annotation['module']) . "', action: '" . str_replace('/', '\/', $annotation['action']) . "'.*\\]/";
                preg_match($matchFileContent, $fileContent, $matchResult);

                if (! empty($matchResult[0]) && ! str_contains($matchResult[0], 'i18nActionName')) {
                    $i18nTxt = I18nHelper::translateArr($name, true);
                    $matchContent = "#[OrgPermission(module: '" . $annotation['module'] . "', action: '" . $annotation['action'] . "'";
                    $fileSubContent = str_replace($matchContent, $matchContent . ', i18nActionName: ' . $i18nTxt, $matchResult[0]);

                    $fileContent = str_replace($matchResult[0], $fileSubContent, $fileContent);

                    file_put_contents($fileName, $fileContent);
                }
            }
        }
    }

    protected function linkLibraryPermission()
    {
        //得到类方法的所有注解
        $methods = AnnotationCollector::getMethodsByAnnotation(LinkLibraryPermission::class);

        foreach ($methods as $value) {
            $tmp = (array) $value;
            $annotation = (array) $value['annotation'];

            // 得到文件名和文件内容
            $reflection = new ReflectionClass($value['class']);
            $fileName = $reflection->getFileName();
            if (str_contains($fileName, 'runtime/container/proxy')) {
                $file = basename($fileName);
                $fileName = str_replace('_', '/', $file);
                $fileName = str_replace('App/', 'app/', $fileName);
                $fileName = str_replace('.proxy.php', '.php', BASE_PATH . '/' . $fileName);
                if (! file_exists($fileName)) {
                    $this->line('文件(' . $file . ')不存在: ' . $fileName, 'error');
                    continue;
                }
            }
            $fileContent = file_get_contents($fileName);

            // 自动补全原文件
            if (! empty($annotation['name']) && empty($annotation['i18nName'])) {
                $moduleNameArr = explode(':', $annotation['name']);
                $name = end($moduleNameArr);
                if (! empty($annotation['port'])) {
                    $matchContent = "/#\\[LinkLibraryPermission\\(port: '(\\w+)', name: '" . $annotation['name'] . "',/";
                    preg_match($matchContent, $fileContent, $matchArr);
                    if (empty($matchArr[0])) {
                        $matchContent = "/#\\[LinkLibraryPermission\\(name: '" . $annotation['name'] . "',/";
                        preg_match($matchContent, $fileContent, $matchArr);
                        if (empty($matchArr[0])) {
                            continue;
                        }
                        $matchContent = $matchArr[0];
                    } else {
                        $matchContent = $matchArr[0];
                    }
                } else {
                    $matchContent = "/#\\[LinkLibraryPermission\\(name: '" . $annotation['name'] . "',/";
                }

                $i18nTxt = I18nHelper::translateArr($name, true);

                $fileContent = str_replace($matchContent, $matchContent . ' i18nName: ' . $i18nTxt . ',', $fileContent);

                file_put_contents($fileName, $fileContent);
            }
        }
    }

    protected function dtoI18n()
    {
        //得到类方法的所有注解
        $classes = AnnotationCollector::getClassesByAnnotation(ExcelData::class);
        foreach ($classes as $key => $value) {
            $tmp = (array) $value;

            // 得到文件名和文件内容
            $reflection = new ReflectionClass($key);
            $fileName = $reflection->getFileName();
            $fileContent = file_get_contents($fileName);

            $cases = AnnotationCollector::get($key)['_p'];
            foreach ($cases as $k => $v) {
                $attributes = (array) $v;

                foreach ($attributes as $attribute) {
                    $attributeArr = (array) $attribute;

                    // 自动补全原文件（字段名）
                    if (! empty($attributeArr['value']) && empty($attributeArr['i18nValue'])) {
                        $i18nTxt = I18nHelper::translateArr($attributeArr['value'], true);

                        $matchContent = "#[ExcelProperty(value: '" . $attributeArr['value'] . "'";
                        if (str_contains($fileContent, $matchContent . ')]')) {
                            $fileContent = str_replace($matchContent . ')]', $matchContent . ', i18nValue: ' . $i18nTxt . ')]', $fileContent);
                        } else {
                            $fileContent = str_replace($matchContent, $matchContent . ', i18nValue: ' . $i18nTxt, $fileContent);
                        }
                        file_put_contents($fileName, $fileContent);
                    }

                    // 自动补全原文件（示例数据包含中文）
                    if (! empty($attributeArr['demo']) && empty($attributeArr['i18nDemo']) && preg_match('/[\x{4e00}-\x{9fa5}]+/u', $attributeArr['demo'])) {
                        $i18nTxt = I18nHelper::translateArr($attributeArr['demo'], true);

                        $matchContent = "#[ExcelProperty(value: '" . $attributeArr['value'] . "', index: " . $attributeArr['index'] . ", demo: '" . $attributeArr['demo'] . "'";
                        if (str_contains($fileContent, $matchContent . ')]')) {
                            $fileContent = str_replace($matchContent . ')]', $matchContent . ', i18nDemo: ' . $i18nTxt . ')]', $fileContent);
                        } else {
                            $fileContent = str_replace($matchContent, $matchContent . ', i18nDemo: ' . $i18nTxt, $fileContent);
                        }
                        file_put_contents($fileName, $fileContent);
                    }

                    // 自动补全原文件（示例数据）
                    if (! empty($attributeArr['tip']) && empty($attributeArr['i18nTip'])) {
                        $matchContent = ", tip: '" . $attributeArr['tip'] . "'";
                        if (substr_count($fileContent, $matchContent) > 1) {
                            $this->line($attributeArr['tip'] . ' 这个tip出现了至少两次，请检查，不要输入通用意义的提示。', 'error');
                            CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
                            return;
                        }

                        $i18nTxt = I18nHelper::translateArr($attributeArr['tip'], true);
                        if (str_contains($fileContent, $matchContent . ')]')) {
                            $fileContent = str_replace($matchContent . ')]', $matchContent . ', i18nTip: ' . $i18nTxt . ')]', $fileContent);
                        } else {
                            $fileContent = str_replace($matchContent, $matchContent . ', i18nTip: ' . $i18nTxt, $fileContent);
                        }
                        file_put_contents($fileName, $fileContent);
                    }
                }
            }
        }
    }

    protected function dictI18n()
    {
        //得到类方法的所有注解
        $classes = AnnotationCollector::getClassesByAnnotation(DictType::class);
        foreach ($classes as $key => $value) {
            $tmp = (array) $value;

            // 得到文件名和文件内容
            $reflection = new ReflectionClass($key);
            $fileName = $reflection->getFileName();
            $fileContent = file_get_contents($fileName);

            // 自动补全原文件
            if (! empty($tmp['name']) && empty($tmp['i18nName'])) {
                $matchContent = "#[DictType(name: '" . $tmp['name'] . "',";
                $i18nTxt = I18nHelper::translateArr($tmp['name'], true);

                $fileContent = str_replace($matchContent, $matchContent . ' i18nName: ' . $i18nTxt . ',', $fileContent);
                file_put_contents($fileName, $fileContent);
            }

            $cases = (new ReflectionClass($key))->getReflectionConstants();
            foreach ($cases as $k => $v) {
                $reflection = new ReflectionEnumBackedCase($key, $v->getName());
                $attributes = $reflection->getAttributes();
                foreach ($attributes as $attribute) {
                    $attributeArr = (array) $attribute->newInstance();

                    // 自动补全原文件
                    if (! empty($attributeArr['label']) && empty($attributeArr['i18nLabel'])) {
                        $i18nTxt = I18nHelper::translateArr($attributeArr['label'], true);

                        $matchContent = "#[DictData(label: '" . $attributeArr['label'] . "'";
                        if (str_contains($fileContent, $matchContent . ')]')) {
                            $fileContent = str_replace($matchContent . ')]', $matchContent . ', i18nLabel: ' . $i18nTxt . ')]', $fileContent);
                        } else {
                            $fileContent = str_replace($matchContent, $matchContent . ', i18nLabel: ' . $i18nTxt, $fileContent);
                        }
                        file_put_contents($fileName, $fileContent);
                    }
                }
            }
        }
    }

    protected function codeI18n()
    {
        //得到类方法的所有注解
        $classes = AnnotationCollector::getClassesByAnnotation(EnumCodePrefix::class);
        foreach ($classes as $key => $value) {
            // 得到文件名和文件内容
            $reflection = new ReflectionClass($key);
            $fileName = $reflection->getFileName();
            $fileContent = file_get_contents($fileName);

            $cases = (new ReflectionClass($key))->getReflectionConstants();
            foreach ($cases as $k => $v) {
                $reflection = new ReflectionEnumBackedCase($key, $v->getName());
                $attributes = $reflection->getAttributes();
                foreach ($attributes as $attribute) {
                    $attributeArr = (array) $attribute->newInstance();

                    // 自动补全原文件
                    if (! empty($attributeArr['msg']) && empty($attributeArr['i18nMsg'])) {
                        $matchContent = "#[EnumCode(msg: '" . $attributeArr['msg'] . "'";
                        $i18nTxt = I18nHelper::translateArr($attributeArr['msg'], true);

                        $fileContent = str_replace($matchContent, $matchContent . ', i18nMsg: ' . $i18nTxt, $fileContent);
                        file_put_contents($fileName, $fileContent);
                    }
                }
            }
        }
    }

    protected function i18n()
    {
        //得到类方法的所有注解
        $classes = AnnotationCollector::getClassesByAnnotation(EnumI18nGroup::class);
        foreach ($classes as $key => $value) {
            // 得到文件名和文件内容
            $reflection = new ReflectionClass($key);
            $fileName = $reflection->getFileName();
            $fileContent = file_get_contents($fileName);

            $cases = (new ReflectionClass($key))->getReflectionConstants();
            foreach ($cases as $k => $v) {
                $reflection = new ReflectionEnumBackedCase($key, $v->getName());
                $attributes = $reflection->getAttributes();
                foreach ($attributes as $attribute) {
                    $attributeArr = (array) $attribute->newInstance();

                    // 自动补全原文件
                    if (! empty($attributeArr['txt']) && empty($attributeArr['i18nTxt'])) {
                        $matchContent = "#[EnumI18n(txt: '" . $attributeArr['txt'] . "'";
                        $i18nTxt = I18nHelper::translateArr($attributeArr['txt'], true);

                        $fileContent = str_replace($matchContent, $matchContent . ', i18nTxt: ' . $i18nTxt, $fileContent);
                        file_put_contents($fileName, $fileContent);
                    }
                }
            }
        }
    }
}

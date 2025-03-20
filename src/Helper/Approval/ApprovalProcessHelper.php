<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */

namespace Bailing\Helper\Approval;

use Bailing\Helper\ApiHelper;
use Bailing\Helper\OrgConfigHelper;
use Bailing\Helper\Webhook\WebhookInvokeHelper;
use Bailing\JsonRpc\WorkApproval\WorkApprovalServiceInterface;
use Bailing\Model\BailingApprovalModule;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Codec\Json;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class ApprovalProcessHelper
{
    public static function getApprovalModuleList(string $alias, int $orgId): array
    {
        // 获取和参数在同一分类中的列表
        $exist = BailingApprovalModule::query()->where('alias', $alias)->first();
        if(empty($exist)){
            return [];
        }

        if ($exist->sub_cat_alias) {
            $list = BailingApprovalModule::query()->where('sub_cat_alias', $exist->sub_cat_alias)->get()->toArray();
        } else {
            $list = BailingApprovalModule::query()->where('source_type', $exist->source_type)->get()->toArray();
        }

        // 判断form表单的版本号是否有变化
        if (!empty($list)) {
            $config = config('approval');
            foreach ($list as $item) {
                // 如果approval数组中不包含，则跳过
                if (empty($config[$item['alias']])) {
                    continue;
                }
                $approvalService = container()->get($config[$item['alias']]);
                if (!property_exists($approvalService, 'version')) {
                    continue;
                }

                // 如果版本号有变化，则删除缓存
                $newVersion = $approvalService->version ?? 0;
                if (!empty($newVersion) && $newVersion > $item['form_version']) {
                    $newForm = $approvalService->approveForm($item['alias']);
                    if (empty($newForm)) {
                        continue;
                    }

                    // 统一添加上 setting 字段
                    foreach ($newForm as &$formItem) {
                        if (!isset($formItem['setting'])) {
                            $formItem['setting'] = [];
                        }
                        if ($formItem['type'] == 'detail') {
                            foreach ($formItem['formList'] as &$detailItem){
                                if (!isset($detailItem['setting'])) {
                                    $detailItem['setting'] = [];
                                }
                            }
                        }
                    }

                    BailingApprovalModule::query()->where(['id' => $item['id']])->update([
                        'form' => Json::encode($newForm),
                        'form_version' => $newVersion,
                    ]);
                    OrgConfigHelper::setConfig($orgId, 'approval_' . $item['alias'], '');
                }
            }
        }

        return $list;
    }

    public static function initProcess(int $orgId, int $uid, string $alias, array $initData): array
    {
        $params = [
            'org_id' => $orgId,
            'uid' => $uid,
            'alias' => $alias,
            'init_data' => $initData,
        ];
        try {
            $initResult = WebhookInvokeHelper::invokeService('work_approval', '/approval_list/init', $params);
        } catch (\Exception $e) {
            stdLog()->error('WebhookInvokeHelper invokeService error：', [$params, $e->getMessage()]);
            return ApiHelper::genErrorData('init error');
        }
        if ($initResult) {
            $initAlias = array_column($initData, 'alias');
            foreach ($initAlias as $key => $item) {
                $approvalIds = container()->get(WorkApprovalServiceInterface::class)->call('getApprovalFormId', ['org_id' => $orgId, 'alias' => $item]);
                stdLog()->info('$approvalIds$approvalIds', [$approvalIds]);
                if (ApiHelper::checkDataOk($approvalIds)) {
                    $tempValue = ['id' => $approvalIds['data']['info']] ?? 0;
                    OrgConfigHelper::setConfig($orgId, 'approval_' . $item, Json::encode($tempValue));
                }
            }
        }
        return [];
    }

    /**
     * 创建表.
     */
    #[Cacheable(prefix: 'bailingApprovalModule-v2', ttl: 86400)]
    public static function createTable(): string
    {
        self::createTableCode();
        return 'bailingApprovalModule';
    }

    public static function createTableCode(): bool
    {
        if (! Schema::hasTable('bailing_approval_module')) {
            Schema::create('bailing_approval_module', function (Blueprint $table) {
                $table->bigIncrements('id');
                // coding in here
                $table->string('name')->default('')->comment('审批模板名称');
                $table->json('i18n_name')->nullable()->comment('审批模板名称多语言');
                $table->json('desc')->nullable()->comment('审批模板说明');
                $table->json('i18n_desc')->nullable()->comment('审批模板说明多语言');
                $table->string('icon')->default('')->comment('审批模板图标地址');
                $table->string('alias')->default('')->comment('审批的别名字符串')->index('idx_alias');
                $table->tinyInteger('approval_type')->default(0)->comment('0审批1是租客审批应用');
                $table->json('form')->nullable()->comment('审批模板表单');
                $table->string('source_type')->default('')->comment('添加模板的应用/模块名称别名');
                $table->string('source_txt')->default('')->comment('添加模板的应用/模块名称');
                $table->string('cat_type')->default('')->comment('分类名称');
                $table->string('sub_cat_type')->default('')->comment('子分类名称');
                $table->string('sub_cat_alias')->default('')->comment('子分类别名');
                $table->tinyInteger('start_user_type')->default(2)->comment('0所有人可以发起，1指定人发起，2所有人不能发起');

                $table->string('created_name', 100)->default('')->comment('创建数据的人员名字');
                $table->string('updated_name', 100)->default('')->comment('最后修改数据的人员名字');
                $table->datetimes();
                $table->softDeletes();
                $table->comment('审批模板源数据');
            });
        }
        if (! Schema::hasColumn('bailing_approval_module', 'status')) {
            Schema::table('bailing_approval_module', function (Blueprint $table) {
                $table->tinyInteger('status')->default(1)->comment('1显示0隐藏')->after('alias');
            });
        }
        if (! Schema::hasColumn('bailing_approval_module', 'i18n_name')) {
            Schema::table('bailing_approval_module', function (Blueprint $table) {
                $table->json('i18n_name')->nullable()->comment('审批模板名称多语言')->after('name');
            });
        }
        if (! Schema::hasColumn('bailing_approval_module', 'desc')) {
            Schema::table('bailing_approval_module', function (Blueprint $table) {
                $table->json('desc')->nullable()->comment('审批模板说明')->after('name');
            });
        }
        if (! Schema::hasColumn('bailing_approval_module', 'i18n_desc')) {
            Schema::table('bailing_approval_module', function (Blueprint $table) {
                $table->json('i18n_desc')->nullable()->comment('审批模板说明多语言')->after('desc');
            });
        }
        if (! Schema::hasColumn('bailing_approval_module', 'i18n_source_txt')) {
            Schema::table('bailing_approval_module', function (Blueprint $table) {
                $table->json('i18n_source_txt')->nullable()->comment('添加模板的应用/模块名称多语言')->after('source_txt');
            });
        }
        if (! Schema::hasColumn('bailing_approval_module', 'i18n_cat_type')) {
            Schema::table('bailing_approval_module', function (Blueprint $table) {
                $table->json('i18n_cat_type')->nullable()->comment('分类名称多语言')->after('cat_type');
            });
        }
        if (! Schema::hasColumn('bailing_approval_module', 'i18n_sub_cat_type')) {
            Schema::table('bailing_approval_module', function (Blueprint $table) {
                $table->json('i18n_sub_cat_type')->nullable()->comment('子分类名称多语言')->after('sub_cat_type');
            });
        }
        if (! Schema::hasColumn('bailing_approval_module', 'form_version')) {
            Schema::table('bailing_approval_module', function (Blueprint $table) {
                $table->integer('form_version')->default(0)->comment('form表单的版本号')->after('form');
            });
        }

        return true;
    }

    /**
     * 创建表.
     */
    #[Cacheable(prefix: 'bailingApprovalCategory-v2', ttl: 86400)]
    public static function createCategoryTable(): string
    {
        self::createCategoryTableCode();
        return 'bailingApprovalCategory';
    }

    public static function createCategoryTableCode(): bool
    {
        if (! Schema::hasTable('bailing_approval_category')) {
            Schema::create('bailing_approval_category', function (Blueprint $table) {
                $table->bigIncrements('id');
                // coding in here
                $table->string('name')->default('')->comment('审批类别名称');
                $table->json('i18n_name')->nullable()->comment('审批类别名称多语言');
                $table->string('alias')->default('')->comment('审批类别别名')->index('idx_alias');
                $table->string('source_txt')->default('')->comment('添加模板的应用/模块名称');
                $table->json('i18n_source_txt')->nullable()->comment('添加模板的应用/模块名称多语言');

                $table->string('created_name', 100)->default('')->comment('创建数据的人员名字');
                $table->string('updated_name', 100)->default('')->comment('最后修改数据的人员名字');
                $table->datetimes();
                $table->softDeletes();
                $table->comment('审批类别表');
            });
        }
        if (! Schema::hasColumn('bailing_approval_category', 'cate_sort')) {
            Schema::table('bailing_approval_category', function (Blueprint $table) {
                $table->integer('cate_sort')->default(0)->comment('排序')->after('i18n_source_txt');
            });
        }

        return true;
    }

    /**
     * 替换审批表单模板.
     * @param string $alias
     * @param array $formValue
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function replaceFormTemplate(string $alias, array $formValue): array
    {
        $config = config('approval');
        $approvalService = container()->get($config[$alias]);
        $newForm = $approvalService->approveForm($alias);
        if (empty($newForm)) {
            throw new \Exception('approval form return empty');
        }

        return self::replaceFormTemplateDo($newForm, $formValue);
    }

    public static function replaceFormTemplateDo(array $newForm, array $formValue): array
    {
        foreach ($newForm as &$item) {
            $value = $formValue[$item['key']] ?? '';
            if (empty($value)) {
                $item['value'] = '';
                continue;
            }

            // 优先处理明细/表格，如果参数需要自定义一些参数，则合并参数。例如设置 show_value
            if ($item['type'] == 'detail') {
                if (!isset($value['value'])) {
                    throw new \Exception('明细/表格必须有value属性');
                }
                foreach ($value['value'] as $tableValue) {
                    $item['value'][] = self::replaceFormTemplateDo($item['formList'], $tableValue);
                }
                unset($value['value']);

                // 如果存在 setting 设置参数，则优先合并掉两者的setting
                if (!empty($value['setting'])) {
                    if (!empty($item['setting'])) {
                        $item['setting'] = array_merge($item['setting'], $value['setting']);
                    } else {
                        $item['setting'] = $value['setting'];
                    }
                    unset($value['setting']);
                }
                $item = array_merge($item, $value);
            } else if (is_array($value) && !empty($value['value'])) {
                // 如果是数组，存在 setting 设置参数，则优先合并掉两者的setting
                if (!empty($value['setting'])) {
                    if (!empty($item['setting'])) {
                        $item['setting'] = array_merge($item['setting'], $value['setting']);
                    } else {
                        $item['setting'] = $value['setting'];
                    }
                    unset($value['setting']);
                }
                $item = array_merge($item, $value);
            } else {
                $item['value'] = $value;
            }
        }
        return $newForm;
    }
}

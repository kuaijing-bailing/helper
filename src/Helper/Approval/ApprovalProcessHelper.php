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
    public static function getApprovalModuleList(string $alias): array
    {
        // 获取和参数在同一分类中的列表
        $exist = BailingApprovalModule::query()->where('alias', $alias)->first();
        $list = [];
        if ($exist) {
            if ($exist->sub_cat_alias) {
                $list = BailingApprovalModule::query()->where('sub_cat_alias', $exist->sub_cat_alias)->get()->toArray();
            } else {
                $list = BailingApprovalModule::query()->where('source_type', $exist->source_type)->get()->toArray();
            }
        }
        return ApiHelper::genSuccessData(['list' => $list]);
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
    #[Cacheable(prefix: 'bailingApprovalModule', ttl: 86400)]
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
}

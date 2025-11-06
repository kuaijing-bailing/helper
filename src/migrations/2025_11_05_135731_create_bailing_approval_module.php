<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
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
                $table->string('alias')->default('')->comment('审批的别名字符串')->index();
                $table->tinyInteger('status')->default(1)->comment('1显示0隐藏');
                $table->tinyInteger('approval_type')->default(0)->comment('0审批1是租客审批应用');
                $table->json('form')->nullable()->comment('审批模板表单');
                $table->integer('form_version')->default(0)->comment('form表单的版本号');
                $table->string('source_type')->default('')->comment('添加模板的应用/模块名称别名');
                $table->string('source_txt')->default('')->comment('添加模板的应用/模块名称');
                $table->json('i18n_source_txt')->nullable()->comment('添加模板的应用/模块名称多语言');
                $table->string('cat_type')->default('')->comment('分类名称');
                $table->json('i18n_cat_type')->nullable()->comment('分类名称多语言');
                $table->string('sub_cat_type')->default('')->comment('子分类名称');
                $table->json('i18n_sub_cat_type')->nullable()->comment('子分类名称多语言');
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bailing_approval_module');
    }
};

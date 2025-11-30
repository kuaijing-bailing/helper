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
        if (! Schema::hasTable('bailing_approval_category')) {
            Schema::create('bailing_approval_category', function (Blueprint $table) {
                $table->bigIncrements('id');
                // coding in here
                $table->string('name')->default('')->comment('审批类别名称');
                $table->json('i18n_name')->nullable()->comment('审批类别名称多语言');
                $table->string('alias')->default('')->comment('审批类别别名')->index();
                $table->string('source_txt')->default('')->comment('添加模板的应用/模块名称');
                $table->json('i18n_source_txt')->nullable()->comment('添加模板的应用/模块名称多语言');
                $table->integer('cate_sort')->default(0)->comment('排序');

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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bailing_approval_category');
    }
};

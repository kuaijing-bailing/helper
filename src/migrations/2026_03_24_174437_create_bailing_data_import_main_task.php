<?php

declare(strict_types=1);
/**
 * This file is part of Bailing.
 *
 * @link     https://www.yunbailing.cn
 * @document https://www.yunbailing.cn/document/
 * @contact  www.yunbailing.cn 7*12 9:00-21:00
 * @license  https://www.yunbailing.cn/LICENSE
 */
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
	    if (! Schema::hasTable('bailing_data_import_main_task')) {
		    Schema::create('bailing_data_import_main_task', function (Blueprint $table) {
			    $table->bigIncrements('id');
			    $table->integer('org_id')->index();
			    $table->string('task_id', 40)->comment('任务id')->index();
			    $table->string('service_name', 100)->default('')->comment('服务名称');
			    $table->string('file_name')->comment('文件名称');
			    $table->integer('data_total')->default(0)->comment('数据总条数');
			    $table->integer('success_num')->default(0)->comment('成功导入条数');
			    $table->integer('error_num')->default(0)->comment('失败导入条数');
			    $table->integer('created_uid')->default(0)->comment('数据创建者uid');
			    $table->string('created_name', 100)->default('')->comment('数据创建者名称');
			    $table->timestamps();
			    $table->softDeletes();
			    $table->comment('数据导入主任务表');
		    });
	    }

	    if (! Schema::hasTable('bailing_data_import_sub_task')) {
		    Schema::create('bailing_data_import_sub_task', function (Blueprint $table) {
			    $table->bigIncrements('id');
			    $table->integer('task_id')->index()->comment('主任务id');
			    $table->string('sub_task_id', 40)->nullable()->comment('子任务id');
			    $table->json('data')->nullable()->comment('数据');
			    $table->json('error_data')->nullable()->comment('导入后数据，包含失败原因和导入成功数据');
			    $table->tinyInteger('status')->default(0)->comment('状态0：待处理 1：成功 2:失败');
			    $table->softDeletes();
			    $table->timestamps();
			    $table->comment('数据导入子任务表');
		    });
	    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_import_main_task');
    }
};

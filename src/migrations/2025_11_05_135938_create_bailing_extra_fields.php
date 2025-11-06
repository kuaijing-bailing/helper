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
        if (! Schema::hasTable('bailing_extra_fields')) {
            Schema::create('bailing_extra_fields', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('org_id')->index();
                $table->string('alias')->comment('功能alias')->index();
                // coding in here
                $table->string('fields_name')->default('')->comment('字段名称')->index();
                $table->string('fields_type')->default('')->comment('字段类型')->index();
                $table->string('key')->default('')->comment('拓展字段key值')->index();
                $table->tinyInteger('fill')->default(0)->comment('是否必填');
                $table->string('default_value')->default('')->comment('默认值');
                $table->integer('min_length')->default(0)->comment('最小长度');
                $table->integer('max_length')->default(0)->comment('最大长度');
                $table->string('placeholder')->default('')->comment('占位符');
                $table->json('option_value')->nullable()->comment('选项值');
                $table->tinyInteger('date_type')->default(0)->comment('日期类型,1日期2日期+时间3时间');
                $table->integer('option_max')->default(0)->comment('可选择选项最大数或允许上传文件最大数');
                $table->string('file_type')->default('')->comment('允许上传文件类型');
                $table->integer('sort')->default(0)->comment('排序字段，越大越靠前');

                $table->datetimes();
                $table->integer('created_uid')->default(0)->comment('数据创建者uid');
                $table->integer('updated_uid')->default(0)->comment('数据最后修改者uid');
                $table->string('created_name', 100)->default('')->comment('数据创建者名称');
                $table->string('updated_name', 100)->default('')->comment('数据最后修改者名称');
                $table->softDeletes();
                $table->comment('拓展字段表');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bailing_extra_fields');
    }
};

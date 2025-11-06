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
        if (! Schema::hasTable('bailing_i18n_translation')) {
            Schema::create('bailing_i18n_translation', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->tinyInteger('type')->comment('数据类型（0：i18n，1：dto）');
                $table->string('group_code', 100)->nullable()->comment('分组编码')->index();
                $table->string('group_name', 100)->nullable()->comment('分组描述')->index();
                $table->string('data_id', 50)->nullable()->comment('数据文件的value，用于区分不同的数据')->index();
                $table->string('value_zh_cn', 1000)->nullable()->comment('简体中文的值，后续搜索用');
                $table->json('value')->nullable()->comment('多语言的值');
                $table->tinyInteger('is_changed')->default(0)->comment('是否在后台修改过，如果没修改，程序则会自动更新');
                $table->timestamps();
                $table->comment('I18n国际化内容表');
            });
        }
        if (! Schema::hasColumn('bailing_i18n_translation', 'is_changed')) {
            Schema::table('bailing_i18n_translation', function (Blueprint $table) {
                $table->tinyInteger('is_changed')->default(0)->comment('是否在后台修改过，如果没修改，程序则会自动更新')->after('value');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bailing_i18n_translation');
    }
};

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
        if (! Schema::hasTable('bailing_translation')) {
            Schema::create('bailing_translation', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('org_id')->comment('机构ID')->index();
                $table->string('table_field', 150)->nullable()->comment('数据表表名和字段名')->index();
                $table->string('data_id', 50)->nullable()->comment('数据表的关联参数，一般为ID')->index();
                $table->json('value')->nullable()->comment('多语言的值');
                $table->tinyInteger('is_changed')->default(0)->comment('是否在后台修改过，如果没修改，程序则会自动更新');
                $table->timestamps();
                $table->comment('国际化内容表');
            });
        }
        if (! Schema::hasColumn('bailing_translation', 'is_changed')) {
            Schema::table('bailing_translation', function (Blueprint $table) {
                $table->tinyInteger('is_changed')->default(0)->comment('是否在后台修改过，如果没修改，程序则会自动更新')->after('value');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bailing_translation');
    }
};

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
        if (! Schema::hasTable('bailing_org_config')) {
            Schema::create('bailing_org_config', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('org_id')->nullable()->comment('机构ID')->index();
                $table->string('index', 100)->nullable()->comment('额外的唯一索引名，例如（项目ID_楼宇ID）')->index();
                $table->string('name', 100)->nullable()->comment('名称')->index();
                $table->text('value')->nullable()->comment('值');
                $table->string('remark', 100)->nullable()->comment('备注');
                $table->timestamps();
                $table->comment('机构配置表');
            });
        } elseif (! Schema::hasColumn('bailing_org_config', 'remark')) {
            Schema::table('bailing_org_config', function (Blueprint $table) {
                $table->string('remark', 100)->nullable()->comment('备注')->after('value');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bailing_org_config');
    }
};

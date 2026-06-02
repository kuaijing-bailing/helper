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
        Schema::table('bailing_extra_fields', function (Blueprint $table) {
            $table->tinyInteger('is_system')->default(0)->comment('是否为系统字段,0否1是')->after('sort');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bailing_extra_fields', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });
    }
};
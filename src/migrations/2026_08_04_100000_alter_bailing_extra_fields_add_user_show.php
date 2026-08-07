<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('bailing_extra_fields') && ! Schema::hasColumn('bailing_extra_fields', 'user_show')) {
            Schema::table('bailing_extra_fields', function (Blueprint $table) {
                $table->tinyInteger('user_show')->default(1)->comment('用户端是否显示,1显示0不显示')->after('is_system');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bailing_extra_fields') && Schema::hasColumn('bailing_extra_fields', 'user_show')) {
            Schema::table('bailing_extra_fields', function (Blueprint $table) {
                $table->dropColumn('user_show');
            });
        }
    }
};

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

// 标记系统后台保存的配置，历史数据默认不标记，避免把普通业务配置当作后台添加.
return new class extends Migration {
    /**
     * 增加来源标记，兼容重复执行；未初始化的服务由配置表初始化逻辑补齐字段.
     */
    public function up(): void
    {
        if (Schema::hasTable('bailing_org_config') && ! Schema::hasColumn('bailing_org_config', 'is_system')) {
            Schema::table('bailing_org_config', function (Blueprint $table) {
                $table->boolean('is_system')->default(false)->comment('是否由系统后台保存')->after('value');
            });
        }
    }

    /**
     * 回滚只移除来源标记，配置值、注释原文和其他数据保留不变.
     */
    public function down(): void
    {
        if (Schema::hasTable('bailing_org_config') && Schema::hasColumn('bailing_org_config', 'is_system')) {
            Schema::table('bailing_org_config', function (Blueprint $table) {
                $table->dropColumn('is_system');
            });
        }
    }
};

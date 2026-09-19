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

// 将编辑器注释原文与业务配置值分开存储，旧 value 保持标准 JSON 或普通文本.
return new class extends Migration {
    /**
     * 为已有机构配置表补充注释原文列，兼容重复执行及未初始化的服务.
     */
    public function up(): void
    {
        if (Schema::hasTable('bailing_org_config') && ! Schema::hasColumn('bailing_org_config', 'value_document')) {
            Schema::table('bailing_org_config', function (Blueprint $table) {
                $table->text('value_document')->nullable()->comment('带字段注释的JSON配置原文')->after('value');
            });
        }
    }

    /**
     * 回滚时移除注释原文列；注释将丢失，业务配置 value 保留不变.
     */
    public function down(): void
    {
        if (Schema::hasTable('bailing_org_config') && Schema::hasColumn('bailing_org_config', 'value_document')) {
            Schema::table('bailing_org_config', function (Blueprint $table) {
                $table->dropColumn('value_document');
            });
        }
    }
};

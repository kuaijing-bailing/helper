<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */

namespace Bailing\Model;

use Carbon\Carbon;

/**
 * @property int $id
 * @property int $org_id
 * @property string $index
 * @property string $name
 * @property string $value
 * @property null|string $value_document 带字段注释的 JSON 原文，仅用于配置编辑器
 * @property bool $is_system 是否由系统后台保存，普通业务更新保留来源
 * @property null|string $remark
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class BailingOrgConfig extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'bailing_org_config';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * 模型属性类型转换，系统后台来源使用布尔值，默认 false.
     */
    protected array $casts = ['id' => 'integer', 'org_id' => 'integer', 'is_system' => 'boolean', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

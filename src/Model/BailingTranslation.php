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

/**
 * @property int $id
 * @property int $org_id 机构ID
 * @property string $table_field 数据表的名称
 * @property string $data_id 数据表的ID
 * @property string $value 多语言的值
 * @property int $is_changed 是否在后台修改过，如果没修改，程序则会自动更新
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class BailingTranslation extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'bailing_translation';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['org_id', 'table_field', 'data_id'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['value' => 'array', 'id' => 'integer', 'org_id' => 'integer', 'is_changed' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

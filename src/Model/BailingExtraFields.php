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
 * @property int $org_id
 * @property string $alias 功能别名
 * @property string $fields_name 字段名称
 * @property string $fields_type 字段类型
 * @property string $key 拓展字段key值
 * @property int $fill 是否必填
 * @property string $default_value 默认值
 * @property int $min_length 最小长度
 * @property int $max_length 最大长度
 * @property string $placeholder 占位符
 * @property string $option_value 选项值
 * @property int $date_type 日期类型,1日期2日期+时间3时间
 * @property int $option_max 可选择选项最大数或允许上传文件最大数
 * @property string $file_type 允许上传文件类型
 * @property int $sort 排序字段，越大越靠前
 * @property int $is_system 是否为系统字段,0否1是
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property int $created_uid 数据创建者uid
 * @property int $updated_uid 数据最后修改者uid
 * @property string $created_name 数据创建者名称
 * @property string $updated_name 数据最后修改者名称
 * @property string $deleted_at
 */
class BailingExtraFields extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'bailing_extra_fields';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'org_id' => 'integer', 'fill' => 'integer', 'min_length' => 'integer', 'max_length' => 'integer', 'date_type' => 'integer', 'option_max' => 'integer', 'sort' => 'integer', 'is_system' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'created_uid' => 'integer', 'updated_uid' => 'integer', 'option_value' => 'array'];

    public static function trimFields(): array
    {
        return ['fields_name', 'alias'];
    }
}

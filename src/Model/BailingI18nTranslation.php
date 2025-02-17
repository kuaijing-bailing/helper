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
 * @property int $type 数据类型（0：i18n，1：dict）
 * @property string $group_code 分组编码
 * @property string $group_name 分组描述
 * @property string $data_id 数据文件的value，用于区分不同的数据
 * @property string $value_zh_cn 简体中文的值，后续搜索用
 * @property string $value 多语言的值
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class BailingI18nTranslation extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'bailing_i18n_translation';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['type', 'group_code', 'data_id'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['value' => 'array', 'id' => 'int', 'type' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

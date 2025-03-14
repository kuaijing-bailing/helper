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
 * @property string $name 审批模板名称
 * @property string $i18n_name 审批模板名称多语言
 * @property string $alias 审批的别名字符串
 * @property string $source_txt 添加模板的应用/模块名称
 * @property int $cate_sort 排序
 * @property string $i18n_source_txt 添加模板的应用/模块名称多语言
 * @property string $created_name 创建数据的人员名字
 * @property string $updated_name 最后修改数据的人员名字
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 */
class BailingApprovalCategory extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'bailing_approval_category';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['cate_sort' => 'integer', 'id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'i18n_name' => 'array', 'i18n_source_txt' => 'array'];
}

<?php

declare(strict_types=1);
/**
 * This file is part of Bailing.
 *
 * @link     https://www.yunbailing.cn
 * @document https://www.yunbailing.cn/document/
 * @contact  www.yunbailing.cn 7*12 9:00-21:00
 * @license  https://www.yunbailing.cn/LICENSE
 */
namespace Bailing\Model;

/**
 * @property int $id
 * @property int $task_id 主任务id
 * @property string $sub_task_id 子任务id
 * @property string $data 数据
 * @property string $error_data 导入后数据，包含失败原因和导入成功数据
 * @property int $status 状态0：待处理 1：成功 2:失败
 * @property string $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class BailingDataImportSubTask extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'bailing_data_import_sub_task';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'task_id' => 'integer', 'status' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'data' => 'array', 'error_data' => 'array'];
}

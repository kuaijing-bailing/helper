<?php

declare(strict_types=1);

namespace Bailing\Model;

/**
 * @property int $id 
 * @property int $org_id 
 * @property string $task_id 任务id
 * @property string $service_name 服务名称
 * @property string $file_name 文件名称
 * @property int $data_total 数据总条数
 * @property int $success_num 成功导入条数
 * @property int $error_num 失败导入条数
 * @property int $created_uid 数据创建者uid
 * @property string $created_name 数据创建者名称
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 * @property string $deleted_at 
 */
class BailingDataImportMainTask extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'bailing_data_import_main_task';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'org_id' => 'integer', 'data_total' => 'integer', 'success_num' => 'integer', 'error_num' => 'integer', 'created_uid' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}

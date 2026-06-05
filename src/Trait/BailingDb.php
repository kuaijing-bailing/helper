<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\Trait;

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Schema\Schema;

/**
 * 数据变更时，记录用户信息.
 *
 * @mixin Model
 *
 * @method static Builder selectExcept(array $excludeFields = [], bool $excludeJson = false) 查询时排除指定字段
 * @method Builder selectExcept(array $excludeFields = [], bool $excludeJson = false) 查询时排除指定字段
 */
trait BailingDb
{
    /**
     * 查询时排除指定字段，select 剩余字段.
     *
     * @param Builder $query 查询构造器（scope 自动注入）
     * @param array $excludeFields 需要排除的字段
     * @param bool $excludeJson 是否同时排除所有 JSON 类型字段，默认 false
     */
    public function scopeSelectExcept(Builder $query, array $excludeFields = [], bool $excludeJson = false): Builder
    {
        $tableName = $this->getTable();

        $columnsTypeArr = cacheData(sprintf('database_table_column_type:%s', $tableName), function () use ($tableName) {
            return array_column(Schema::getColumnTypeListing($tableName), 'data_type', 'column_name');
        }, 86400);

        if ($excludeJson) {
            foreach ($columnsTypeArr as $column => $type) {
                if ($type === 'json') {
                    $excludeFields[] = $column;
                }
            }
        }

        $fields = array_values(array_diff(array_keys($columnsTypeArr), $excludeFields));

        return $query->select($fields);
    }
}

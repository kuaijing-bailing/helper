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

use Bailing\Helper\TranslationHelper;
use Bailing\Model\BailingTranslation;

/**
 * 国际化的model挂载.
 */
trait TranslationTrait
{
    /**
     * leftJoin方式的查询i18n内容.
     * @param array $i18nFieldArr i18n字段数组，为空则读取model的i18n属性
     * @param array $fieldArr 要查询的本表的字段数组，默认为全部字段
     * @param string $relationField 关联的字段，默认为自增ID
     */
    public static function i18nQuery(array $i18nFieldArr = [], array $fieldArr = [], string $relationField = 'id', int $orgId = 0)
    {
        $model = new self();
        // 如果传参为空，则读取model的i18n属性
        if (empty($i18nFieldArr)) {
            $i18nArr = $model->i18n;
            if (! empty($i18nArr)) {
                foreach ($i18nArr as $field) {
                    if (str_ends_with($field, '_i18n')) {
                        $i18nFieldArr[] = str_replace('_i18n', '', $field);
                    }
                }
            }
        }
        $query = self::query();
        if (! cfg('open_internationalize')) {
            if (! empty($fieldArr)) {
                $query->select($fieldArr);
            }
            return $query;
        }
        $table = (new self())->getTable();

        if (empty($fieldArr)) {
            $fieldArr = $table . '.*';
        } else {
            foreach ($fieldArr as &$item) {
                $item = $table . '.' . $item;
            }
        }
        $query->addSelect($fieldArr);

        foreach ($i18nFieldArr as $field) {
            $tmpTableName = 'i18n_' . $field;
            $query->leftJoin('bailing_translation as ' . $tmpTableName, function ($join) use ($tmpTableName, $table, $field, $relationField, $orgId) {
                $join->on($tmpTableName . '.data_id', '=', $table . '.' . $relationField)
                    ->where($tmpTableName . '.table_field', $table . '_' . $field)
                    ->where($tmpTableName . '.org_id', $orgId);
            })->addSelect(['i18n_' . $field . '.value as ' . $field . '_i18n']);
        }
        return $query;
    }

    /**
     * 转义数组，得到i18n的值.
     * @param ?array $dataList 数据列表
     * @param int $orgId 机构ID，默认为0
     */
    public static function i18nConvert(?array $dataList, int $orgId = 0): array
    {
        if (empty($dataList)) {
            return $dataList;
        }

        $tableName = (new self())->getTable();
        $tableI18n = config('translation.i18n_table.' . $tableName);
        if (empty($tableI18n)) {
            return $dataList;
        }

        return TranslationHelper::i18nConvert($dataList, $tableName, $tableI18n['i18n'] ?? $tableI18n[0], $tableI18n['relation'] ?? $tableI18n[1], $orgId);
    }

    /**
     * 保存多语言内容.
     */
    public static function saveTranslation(int $orgId, string $field, int|string $dataId, array $value, bool $isCover = true, bool $isUserChange = false): bool
    {
        $tableName = (new self())->getTable();

        if (isDevEnv()) {
            $tableI18n = config('translation.i18n_table.' . $tableName);
            if (empty($tableI18n)) {
                throw new \Exception(sprintf('需要先将该表(%s)绑定至多语言文件', $tableName));
            }
            if (empty($tableI18n['i18n']) || ! in_array($field, $tableI18n['i18n'])) {
                throw new \Exception(sprintf('需要先将该字段(%s)绑定至多语言文件的i18n字段', $field));
            }
        }

        return TranslationHelper::saveTranslation($orgId, $tableName . '_' . $field, $dataId, $value, $isCover, $isUserChange);
    }

    /**
     * 自动遍历数组保存多语言内容.
     */
    public static function autoSaveTranslation(int|string $dataId, array $postArr, int $orgId = 0): bool
    {
        if (empty($postArr)) {
            return true;
        }

        $tableName = (new self())->getTable();

        return TranslationHelper::autoSaveTranslation($tableName, $dataId, $postArr, $orgId);
    }

    /**
     * with方式的查询i18n内容（只能通过自增ID），返回的字段为所有字段，需要自行处理.
     * @param array $i18nFieldArr i18n字段数组
     */
    public static function i18nQuery2(array $i18nFieldArr)
    {
        $query = self::query();
        if (! cfg('open_internationalize')) {
            return $query;
        }
        $table = (new self())->getTable();

        foreach ($i18nFieldArr as $key => $field) {
            $showName = 'i18nValue' . $key;
            $query->with([$showName => function ($query) use ($table, $field) {
                return $query->where('table_field', $table . '_' . $field);
            }]);
        }
        return $query;
    }

    public function i18nValue0()
    {
        return $this->hasOne(BailingTranslation::class, 'data_id', 'id');
    }

    public function i18nValue1()
    {
        return $this->hasOne(BailingTranslation::class, 'data_id', 'id');
    }

    public function i18nValue2()
    {
        return $this->hasOne(BailingTranslation::class, 'data_id', 'id');
    }

    public function i18nValue3()
    {
        return $this->hasOne(BailingTranslation::class, 'data_id', 'id');
    }

    public function i18nValue4()
    {
        return $this->hasOne(BailingTranslation::class, 'data_id', 'id');
    }

    public function i18nValue5()
    {
        return $this->hasOne(BailingTranslation::class, 'data_id', 'id');
    }
}

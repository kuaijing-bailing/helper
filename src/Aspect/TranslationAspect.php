<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */

namespace Bailing\Aspect;

use Bailing\Helper\Intl\DateTimeHelper;
use Bailing\Helper\TranslationHelper;
use Carbon\Carbon;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

#[Aspect]
class TranslationAspect extends AbstractAspect
{
    // 要切入的类或 Trait，可以多个，亦可通过 :: 标识到具体的某个方法，通过 * 可以模糊匹配
    public array $classes = [
        'Hyperf\Database\Model\Builder::get',
    ];

    // 要切入的注解，具体切入的还是使用了这些注解的类，仅可切入类注解和类方法注解
    public array $annotations = [];

    /**
     * 按配置补齐模型字段翻译及创建、更新时间，仅提取翻译必需字段，避免重复展开模型与关联。
     *
     * @return mixed 保留原查询模型及其国际化字段；空结果原样返回
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $result = $proceedingJoinPoint->process();
        if (empty($result)) {
            return $result;
        }

        $newResult = $result->all();
        if (empty($newResult)) {
            return $result;
        }

        $tableName = $result->offsetGet(0)->getTable();

        // 得到需要i18n的配置项
        $tableI18nConfig = config('translation.i18n_table.' . $tableName);

        $translationRows = [];
        $i18nResult = [];
        if (! empty($tableI18nConfig)) {
            $relationField = $tableI18nConfig['relation'] ?? 'id';
            $fields = array_unique([...$tableI18nConfig['i18n'], $relationField, ...(! empty($tableI18nConfig['isOrg']) ? ['org_id'] : [])]);
            foreach ($newResult as $key => $model) {
                $translationRows[$key] = $this->extractTranslationFields($model, $fields, $relationField);
            }
            $firstRow = reset($translationRows);
            // 关联键未选出或被隐藏时沿用原有不翻译行为，避免访问器或关联懒加载补查该字段。
            if (! empty($firstRow[$relationField])) {
                $i18nResult = TranslationHelper::i18nConvert(
                    array_values($translationRows),
                    $tableName,
                    $tableI18nConfig['i18n'],
                    $relationField,
                    ! empty($tableI18nConfig['isOrg']) ? $firstRow['org_id'] ?? 0 : 0
                );
                $i18nResult = array_column($i18nResult, null, $relationField);
            }
        }

        // 需要日期时间转换的字段
        $dateTimeFieldArr = ['created_at', 'updated_at'];
        // 重组i18n的结果
        foreach ($newResult as $key => $model) {
            if ($model instanceof Model) {
                if (! empty($tableI18nConfig)) {
                    // 关联键已经提取，回填时复用它，避免每个翻译字段再次触发主键访问器。
                    $relationValue = $translationRows[$key][$relationField] ?? null;
                    foreach ($tableI18nConfig['i18n'] as $item) {
                        $tmpField = 'i18n_' . $item;
                        if ($relationValue !== null && ! empty($i18nResult[$relationValue][$tmpField])) {
                            $model->{$tmpField} = $i18nResult[$relationValue][$tmpField];
                        }
                    }
                }
                foreach ($dateTimeFieldArr as $item) {
                    $value = $model->getAttribute($item);
                    if (! empty($value)) {
                        $tmpField = 'i18n_' . $item;
                        if ($value instanceof Carbon) {
                            $model->{$tmpField} = DateTimeHelper::getDateTimeByUnixTimestamp($value->getTimestamp(), 'datetime');
                        } else {
                            $model->{$tmpField} = DateTimeHelper::getDateTimeByUnixTimestamp(DateTimeHelper::strtotime($value), 'datetime');
                        }
                    }
                }
            }
        }

        return new Collection($newResult);
    }

    /**
     * 提取字段翻译所需的属性，保留访问器、类型转换和字段可见性，不展开无关附加属性或关联。
     *
     * @param Model $model 本次查询得到的模型
     * @param array<string> $fields 翻译字段、关联键及按需读取的机构字段
     * @param string $relationField 翻译数据关联键，未选出或不可见时无需提取其他翻译字段
     * @return array<string, mixed> 当前模型允许输出的翻译源数据
     */
    private function extractTranslationFields(Model $model, array $fields, string $relationField): array
    {
        $visible = $model->getVisible();
        $fields = $visible === [] ? $fields : array_intersect($fields, $visible);
        $fields = array_diff($fields, $model->getHidden());
        // setVisible([]) 表示不限制字段，不能用它表达空交集，否则会重新展开全部属性。
        if ($fields === [] || ! in_array($relationField, $fields, true)
            || (! array_key_exists($relationField, $model->getAttributes()) && ! $model->hasAppended($relationField))) {
            return [];
        }
        return (clone $model)->setVisible($fields)->attributesToArray();
    }
}

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
use Bailing\Helper\Intl\NumberFormatHelper;
use Carbon\Carbon;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

#[Aspect]
class IntlAspect extends AbstractAspect
{
    // 要切入的类或 Trait，可以多个，亦可通过 :: 标识到具体的某个方法，通过 * 可以模糊匹配
    public array $classes = [
        'Hyperf\Database\Model\Builder::get',
    ];

    // 要切入的注解，具体切入的还是使用了这些注解的类，仅可切入类注解和类方法注解
    public array $annotations = [];

    /**
     * 按配置补齐数值、币种和日期展示，在本次处理内复用同一模型未被回填的源字段。
     *
     * @return mixed 保留原模型及其国际化字段；空结果原样返回
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

        // isset/empty 与随后取值都会触发访问器；在本次切面执行内复用已读结果，包括 null 和零值。
        $fieldValues = [];
        $readField = static function (Model $model, string $field) use (&$fieldValues): mixed {
            $modelId = spl_object_id($model);
            if (! array_key_exists($field, $fieldValues[$modelId] ?? [])) {
                $fieldValues[$modelId][$field] = $model->getAttribute($field);
            }
            return $fieldValues[$modelId][$field];
        };

        // 得到需要数字文字展示的配置项
        $tableCurrencyConfig = config('translation.number.' . $tableName);
        if (! empty($tableCurrencyConfig)) {
            foreach ($newResult as $model) {
                if ($model instanceof Model) {
                    foreach ($tableCurrencyConfig as $key => $item) {
                        // 如果是数组，则保留2位小数
                        if (is_numeric($key)) {
                            $tmpDecimals = 2;
                            $tmpField = $item;
                        } else {
                            $tmpDecimals = $item;
                            $tmpField = $key;
                        }

                        $value = $readField($model, $tmpField);
                        if ($value !== null) {
                            $tmpNewField = 'i18n_' . $tmpField;
                            $model->{$tmpNewField} = NumberFormatHelper::getFormatNumberArray(number: $value, decimals: $tmpDecimals);
                            unset($fieldValues[spl_object_id($model)][$tmpNewField]);
                        }
                    }
                }
            }
        }

        // 得到需要货币文字展示的配置项
        $tableCurrencyConfig = config('translation.currency.' . $tableName);
        if (! empty($tableCurrencyConfig)) {
            $tmpCodeField = $tableCurrencyConfig['currency'] ?? 'currency';

            foreach ($newResult as $model) {
                if ($model instanceof Model) {
                    foreach ($tableCurrencyConfig['field'] as $key => $item) {
                        // 如果是数组，则保留2位小数
                        if (is_numeric($key)) {
                            $tmpDecimals = 2;
                            $tmpField = $item;
                        } else {
                            $tmpDecimals = $item;
                            $tmpField = $key;
                        }

                        $tmpNewField = 'i18n_' . $tmpField;
                        $value = $readField($model, $tmpField);
                        if ($value !== null) {
                            $tmpNumber = (float) getFormatNumber($value, $tmpDecimals);
                            $model->{$tmpNewField} = NumberFormatHelper::getFormatCurrencyArray($tmpNumber, $readField($model, $tmpCodeField) ?? '');
                            unset($fieldValues[spl_object_id($model)][$tmpNewField]);
                        }
                    }
                }
            }
        }

        // 得到需要日期时间文字展示的配置项 dateTime、date、time
        $tableDateTimeConfig = config('translation.datetime.' . $tableName);
        if (! empty($tableDateTimeConfig)) {
            foreach ($newResult as $model) {
                if ($model instanceof Model) {
                    foreach ($tableDateTimeConfig as $key => $item) {
                        // 如果是数组，则保留2位小数
                        if (is_numeric($key)) {
                            $tmpFormat = 'datetime';
                            $tmpField = $item;
                        } else {
                            $tmpFormat = $item;
                            $tmpField = $key;
                        }

                        // 二维数组
                        if (str_contains($tmpField, '.')) {
                            $tmpFieldArr = explode('.', $tmpField);
                            $parentValue = $readField($model, $tmpFieldArr[0]);
                            if (! empty($parentValue[$tmpFieldArr[1]])) {
                                $tmpValue = $parentValue[$tmpFieldArr[1]];
                                $tmpNewField = 'i18n_' . $tmpFieldArr[1];
                                // 兼容Carbon对象
                                if ($tmpValue instanceof Carbon) {
                                    $model->{$tmpFieldArr[0]} = array_merge($parentValue, [$tmpNewField => DateTimeHelper::getDateTimeByUnixTimestamp($tmpValue->getTimestamp(), $tmpFormat)]);
                                } else {
                                    $model->{$tmpFieldArr[0]} = array_merge($parentValue, [$tmpNewField => DateTimeHelper::getDateTimeByUnixTimestamp(DateTimeHelper::strtotime($tmpValue), $tmpFormat)]);
                                }
                                // 回填嵌套数组后使该字段失效，下一项读取写入访问器处理后的值，保留同一数组内其他日期。
                                unset($fieldValues[spl_object_id($model)][$tmpFieldArr[0]]);
                            }
                        } else {
                            $tmpValue = $readField($model, $tmpField);
                            if (empty($tmpValue)) {
                                continue;
                            }
                            $tmpNewField = 'i18n_' . $tmpField;
                            // 兼容Carbon对象
                            if ($tmpValue instanceof Carbon) {
                                $model->{$tmpNewField} = DateTimeHelper::getDateTimeByUnixTimestamp($tmpValue->getTimestamp(), $tmpFormat);
                            } else {
                                $model->{$tmpNewField} = DateTimeHelper::getDateTimeByUnixTimestamp(DateTimeHelper::strtotime($tmpValue), $tmpFormat);
                            }
                            unset($fieldValues[spl_object_id($model)][$tmpNewField]);
                        }
                    }
                }
            }
        }

        return new Collection($newResult);
    }
}

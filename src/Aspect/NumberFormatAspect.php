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

use Bailing\Helper\Intl\NumberFormatHelper;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

#[Aspect]
class NumberFormatAspect extends AbstractAspect
{
    // 要切入的类或 Trait，可以多个，亦可通过 :: 标识到具体的某个方法，通过 * 可以模糊匹配
    public array $classes = [
        'Hyperf\Database\Model\Builder::get',
    ];

    // 要切入的注解，具体切入的还是使用了这些注解的类，仅可切入类注解和类方法注解
    public array $annotations = [];

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

        // 得到需要货币文字展示的配置项
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

                        if (isset($model->{$tmpField})) {
                            $tmpNumber = (float) getFormatNumber($model->{$tmpField}, $tmpDecimals);
                            $tmpNewField = 'i18n_' . $tmpField;
                            $model->{$tmpNewField} = [
                                'show' => NumberFormatHelper::getFormatNumber($tmpNumber),
                                'spellOut' => NumberFormatHelper::getFormatNumberSpellOut($tmpNumber),
                            ];
                        } else {
                            // 如果一旦不存在该字段，则直接跳出循环。部分情况下，查单个字段。
                            break;
                        }
                    }
                }
            }
        }

        $tableCurrencyConfig = config('translation.currency.' . $tableName);
        if (! empty($tableCurrencyConfig)) {
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

                        $tmpCodeField = $tableCurrencyConfig['currency'] ?? 'currency';

                        $tmpNewField = 'i18n_' . $tmpField;
                        if (isset($model->{$tmpField})) {
                            $tmpNumber = (float) getFormatNumber($model->{$tmpField}, $tmpDecimals);
                            $model->{$tmpNewField} = [
                                'number' => NumberFormatHelper::getFormatNumber($tmpNumber),
                                'show' => NumberFormatHelper::getFormatCurrency($tmpNumber),
                                'spellOut' => NumberFormatHelper::getFormatCurrencySpellOut($tmpNumber, $model->{$tmpCodeField} ?? 'CNY'),
                            ];
                        } else {
                            // 如果一旦不存在该字段，则直接跳出循环。部分情况下，查单个字段。
                            break;
                        }
                    }
                }
            }
        }

        return new Collection($newResult);
    }
}

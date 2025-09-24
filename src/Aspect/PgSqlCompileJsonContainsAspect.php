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

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

/**
 * Pgsql数据库监听.
 */
#[Aspect]
class PgSqlCompileJsonContainsAspect extends AbstractAspect
{
    // 要切入的类或 Trait，可以多个，亦可通过 :: 标识到具体的某个方法，通过 * 可以模糊匹配
    public array $classes = [
        'Hyperf\Database\PgSQL\Query\Grammars\PostgresGrammar::compileJsonContains',
    ];

    // 要切入的注解，具体切入的还是使用了这些注解的类，仅可切入类注解和类方法注解
    public array $annotations = [];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // 获取调用方法时提交的参数
        $arguments = $proceedingJoinPoint->getArguments();

        // 执行原方法
        $result = $proceedingJoinPoint->process();

        if (!empty($arguments[0]) && str_contains($result, '@>') && (
            str_contains($arguments[0], 'build_bind->room')
            || str_contains($arguments[0], 'build_bind->build')
            || str_contains($arguments[0], 'checked_build->room')
            || str_contains($arguments[0], 'module->menu')
            )) {
            $sqlArr = explode('@>', $result);
            $result = 'EXISTS ( SELECT 1 FROM jsonb_array_elements(' . trim($sqlArr[0]) . ') AS elem WHERE elem @> ' . trim($sqlArr[1]) . '::jsonb)';
        }

        return $result;
    }
}

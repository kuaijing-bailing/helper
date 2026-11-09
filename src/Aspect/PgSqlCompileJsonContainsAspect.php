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

use Hyperf\Database\PgSQL\Query\Grammars\PostgresGrammar;
use Hyperf\Database\Query\Expression;
use Hyperf\Database\Query\Grammars\Grammar;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

/** 按候选 JSON 的结构编译 PostgreSQL 包含和交集查询。 */
#[Aspect]
class PgSqlCompileJsonContainsAspect extends AbstractAspect
{
    /** 实测小标量列表直接比较更快，候选项达到此数量时改用哈希查找。 */
    private const HASH_OVERLAP_MIN_CANDIDATES = 32;

    /** @var list<string> 在参数变为占位符之前区分候选值的结构，参数绑定仍由原查询构造器管理。 */
    public array $classes = [Grammar::class . '::whereJsonContains', Grammar::class . '::whereJsonOverlaps'];

    /** 为 PostgreSQL 补齐对象数组的包含判断和任一元素匹配；原生表达式沿用框架编译。 */
    public function process(ProceedingJoinPoint $proceedingJoinPoint): string
    {
        [$query, $where] = $proceedingJoinPoint->getArguments();
        $grammar = $query->getGrammar();
        $value = $where['value'];
        if (! $grammar instanceof PostgresGrammar || $value instanceof Expression) {
            return $proceedingJoinPoint->process();
        }

        if ($proceedingJoinPoint->methodName === 'whereJsonContains') {
            if (is_object($value) || (is_array($value) && ! array_is_list($value))) {
                // 对象可匹配列本身，也可匹配数组中的一个对象；序列化为标量或数组时保留原生包含语义。
                $proceedingJoinPoint->arguments['keys']['where']['value'] = new Expression(<<<'SQL'
                    ANY(ARRAY(SELECT unnest(CASE WHEN jsonb_typeof(v) = 'object' THEN ARRAY[v, jsonb_build_array(v)] ELSE ARRAY[v] END)
                        FROM (VALUES (CAST(? AS jsonb))) AS candidate(v)))
                    SQL);
            }
            return $proceedingJoinPoint->process();
        }

        // 保留框架对表前缀和 JSON 路径的转义，末级路径需要取 JSON 而不是文本。
        $field = '(' . str_replace('->>', '->', $grammar->wrap($where['column'])) . ')::jsonb';
        $sql = $this->compileOverlaps($field, $value);
        return $where['not'] ? 'not (' . $sql . ')' : $sql;
    }

    /** 标量交集可以直接使用包含运算；对象和嵌套数组则须比较完整元素，不能把部分包含误作相等。 */
    private function compileOverlaps(string $field, mixed $value): string
    {
        if ($value === null || is_scalar($value)) {
            return "{$field} @> CAST(? AS jsonb)";
        }
        if (is_array($value) && array_is_list($value)) {
            if ($value !== [] && count($value) < self::HASH_OVERLAP_MIN_CANDIDATES
                && array_all($value, static fn ($item) => $item === null || is_scalar($item))) {
                return "{$field} @> ANY(ARRAY(SELECT jsonb_array_elements(CAST(? AS jsonb))))";
            }

            // 保留内层 SELECT：PG 可以只为候选值构建一次哈希表，每份文档按元素查找，避免逐行遍历全部候选值。
            return <<<SQL
                (CASE WHEN {$field} IS NOT NULL THEN EXISTS (
                    SELECT 1 FROM jsonb_array_elements('[]'::jsonb || {$field}) AS doc(item)
                    WHERE (SELECT doc.item IN (SELECT id FROM jsonb_array_elements(CAST(? AS jsonb)) AS candidates(id)))
                ) END)
                SQL;
        }

        // 两个对象比较共同键的完整值；其他类型先视为元素列表，保留数字与字符串的区别。
        return <<<SQL
            (SELECT CASE
                WHEN doc IS NULL THEN NULL
                WHEN jsonb_typeof(doc) = 'object' AND jsonb_typeof(candidate) = 'object' THEN
                    EXISTS (SELECT 1 FROM jsonb_each(candidate) AS entry WHERE doc -> entry.key = entry.value)
                ELSE EXISTS (
                    SELECT 1 FROM jsonb_array_elements('[]'::jsonb || doc) AS lhs(item)
                    JOIN jsonb_array_elements('[]'::jsonb || candidate) AS rhs(item) USING (item)
                )
            END FROM (VALUES ({$field}, CAST(? AS jsonb))) AS operands(doc, candidate))
            SQL;
    }
}

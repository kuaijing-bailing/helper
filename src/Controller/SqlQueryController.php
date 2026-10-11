<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */

namespace Bailing\Controller;

use Bailing\Annotation\RateRequest;
use Bailing\Helper\ApiHelper;
use Bailing\Helper\SqlQueryHelper;
use Bailing\Middleware\SystemMiddleware;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Controller]
class SqlQueryController
{
    /** 非法 SQL 或额外请求参数的统一中文提示. */
    private const INVALID_QUERY_MESSAGE = '仅支持带 WHERE 的单表 SELECT，可使用 ORDER BY；最多返回前20条。禁止分页、写入、删除、DDL、函数、子查询、联表和注释，SQL 不得超过10000字节。';

    /**
     * 为系统超级管理员提供临时只读查询，拒绝分页参数，只返回前 20 条结果.
     * @return array 统一接口响应，包含列名、按列排列的行数据和截断提示标志
     */
    #[PostMapping(path: '/system/querySql')]
    #[Middleware(SystemMiddleware::class)]
    #[RateRequest(waitTimeout: 30, rateKey: 'system_sql_query')]
    public function querySql(): array
    {
        $nowUser = contextGet('nowUser');
        if ((int) ($nowUser->level ?? 0) !== SqlQueryHelper::SUPER_ADMIN_LEVEL || ($nowUser->tokenType ?? '') !== 'system') {
            return ApiHelper::genErrorData('无权访问', ApiHelper::AUTH_ERROR);
        }
        $params = request()->all();
        // 限流键不可由客户端覆盖，分页和任意其他参数一并拒绝.
        if (array_diff(array_keys($params), ['sql']) || ! isset($params['sql']) || ! is_string($params['sql'])) {
            return ApiHelper::genErrorData(self::INVALID_QUERY_MESSAGE);
        }
        $startedAt = microtime(true);
        $status = 'failed';
        try {
            $result = (new SqlQueryHelper())->query($params['sql']);
            $status = 'success';
            return ApiHelper::genSuccessData($result);
        } catch (\InvalidArgumentException) {
            return ApiHelper::genErrorData(self::INVALID_QUERY_MESSAGE);
        } catch (\Throwable) {
            // 不向浏览器透出连接地址、账号、SQL 原文或数据库内部异常.
            return ApiHelper::genErrorData('查询失败：请检查当前服务的业务表名、字段和条件。仅支持真实业务表；查询限时5秒、最多100列及1MB结果，超限或数据库不支持安全执行时会拒绝。');
        } finally {
            logger()->info('system_sql_query', [
                'user_id' => $nowUser->id ?? null,
                'status' => $status,
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);
        }
    }
}

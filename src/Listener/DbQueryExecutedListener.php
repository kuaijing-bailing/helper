<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */

namespace Bailing\Listener;

use Bailing\Helper\LogSecurityHelper;
use Bailing\Helper\SqlLogHelper;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/** 共享 SQL 日志入口，通过组件注解扫描注册一次；只读取配置中心，避免在监听器中查询数据库。 */
#[Listener]
class DbQueryExecutedListener implements ListenerInterface
{
    /** SQL 专用日志通道，明文受系统安全窗口约束。 */
    private LoggerInterface $logger;

    /** 注入 SQL 日志通道，不在构造时缓存明文开关。 */
    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerFactory::class)->get('sql');
    }

    /**
     * 统一监听数据库执行完成事件，应用服务不再重复注册 SQL 监听器。
     * @return array<class-string> 数据库查询事件
     */
    public function listen(): array
    {
        return [QueryExecuted::class];
    }

    /** 逐条检查全局日志窗口，记录完整 SQL；窗口外只隐藏敏感字段值。 */
    public function process(object $event): void
    {
        if ($event instanceof QueryExecuted) {
            $sql = $event->sql;
            $plaintext = LogSecurityHelper::isPlaintextAllowed();
            // 过滤掉临时表
            if (str_contains($sql, 'temp_org_menu') || str_contains($sql, 'temp_system_menu') || str_starts_with($sql, 'insert into `operation_logs`')) {
                return;
            }
            // 只格式化日志副本；脱敏模式不另存原始 bindings，避免旁路泄露。
            $context = [
                'sensitive_log_mode' => $plaintext ? 'plaintext' : 'redacted',
                'connection_name' => $event->connectionName,
                'binding_count' => count($event->bindings),
                'operation' => preg_match('/^\s*(select|insert|update|delete|with|alter|create|drop|begin|commit|rollback)\b/i', $event->sql, $matches) ? strtoupper($matches[1]) : 'OTHER',
            ];
            if ($plaintext) {
                $context['bindings'] = $event->bindings;
            }
            $sql = SqlLogHelper::format($sql, $event->connection->prepareBindings($event->bindings), $plaintext, (string) $event->connection->getDriverName());
            if ($event->time > 2000) {
                $this->logger->warning(sprintf('[%sms] -db- %s', $event->time, $sql), $context);
            } else {
                $this->logger->info(sprintf('[%sms] -db- %s', $event->time, $sql), $context);
            }
        }
    }
}

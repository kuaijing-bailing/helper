<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\Job;

use App\Event\OrgBillApproved;
use Bailing\Annotation\XxlJobTask;
use Bailing\Event\RuntimeFileClear;
use Bailing\Event\RuntimeFileClearEvent;
use Bailing\Helper\FileHelper;
use Hyperf\Di\Annotation\Inject;
use Hyperf\XxlJob\Annotation\XxlJob;
use Hyperf\XxlJob\Handler\AbstractJobHandler;
use Hyperf\XxlJob\Logger\JobExecutorLoggerInterface;
use Hyperf\XxlJob\Requests\RunRequest;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * 缓存文件的清空（日志）每天凌晨2点2分执行，轮询保证每天都能执行到一个.
 */
#[XxlJob('planRuntimeFileClear')]
#[XxlJobTask(jobDesc: '缓存文件的清空（日志）', cron: '0 2 2 * * ?', jobHandler: 'planRuntimeFileClear', routeStrategy: 'ROUND')]
class RuntimeFileClearJob extends AbstractJobHandler
{
    #[Inject]
    protected JobExecutorLoggerInterface $jobExecutorLogger;

    /**
     * 执行任务.
     */
    public function execute(RunRequest $request): void
    {
        $fileList = FileHelper::getDirFiles(RUNTIME_BASE_PATH . '/', false);
        stdLog()->info('清空缓存日志文件开始执行，文件总数：' . strval(count($fileList)));
        $clearCount = 0;
        foreach ($fileList as $item) {
            //程序缓存文件不删除
            if (! str_contains($item['pathName'], '/container/') && ! str_contains($item['pathName'], 'hyperf.pid')) {
                if (env('APP_ENV') == 'dev') {
                    $day = cfg('clear_cache_day') ?: 1;
                } else {
                    $day = cfg('clear_cache_day') ?: 7;
                }
                if ($item['mTime'] < time() - $day * 86400) {
                    stdLog()->info('清空缓存日志文件欲删除：', [$item['pathName'], date('Y-m-d H:i:s', $item['mTime'])]);
                    if (@unlink($item['pathName'])) {
                        ++$clearCount;
                    }
                }
            }
        }
        stdLog()->info('清空缓存日志文件完成执行，删除文件总数：' . strval($clearCount));

        // 删除24小时前的临时缓存文件
        $fileList = FileHelper::getDirFiles(tmpDir() . '/', false);
        stdLog()->info('清空缓存上传文件开始执行，文件总数：' . strval(count($fileList)));
        $clearCount = 0;
        foreach ($fileList as $item) {
            if ($item['mTime'] < time() - 86400) {
                stdLog()->info('清空缓存上传文件欲删除：', [$item['pathName'], date('Y-m-d H:i:s', $item['mTime'])]);
                if (@unlink($item['pathName'])) {
                    ++$clearCount;
                }
            }
        }
        stdLog()->info('清空缓存上传文件完成执行，删除文件总数：' . strval($clearCount));

        container()->get(EventDispatcherInterface::class)->dispatch(new RuntimeFileClear());

    }
}

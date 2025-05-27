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

use Bailing\Helper\ApiHelper;
use Bailing\Helper\FileHelper;
use Bailing\Middleware\SystemMiddleware;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;

#[Controller]
class LogController
{
    /**
     * 查看指定文件夹下内容.
     */
    #[GetMapping(path: '/system/getLogList')]
    #[Middleware(SystemMiddleware::class)]
    public function getLogList(): array
    {
        $path = request()->input('path', '');
        $path = str_replace('\\', '/', $path);
        $path = str_replace('../', '/', $path);

        $nowUser = contextGet('nowUser');
        //系统后台超级管理员才有权限查看
        if ($nowUser->level === 99) {
            $list = FileHelper::getDir(realpath(RUNTIME_BASE_PATH . '/' . $path), realpath(RUNTIME_BASE_PATH) . '/');
            foreach ($list as $key => $value) {
                //如果是隐藏文件，则不返回
                if (str_starts_with($value['fileName'], '.')) {
                    unset($list[$key]);
                }
            }
            $list = arraySort($list, 'fileName');
            return ApiHelper::genSuccessData(['result' => array_values($list)], '获取成功');
        }

        return ApiHelper::genErrorData('暂无权限查看', 4001);
    }

    /**
     * 查看文件下内容.
     */
    #[GetMapping(path: '/system/getLogContent')]
    #[Middleware(SystemMiddleware::class)]
    public function getLogContent(): array
    {
        $page = request()->input('page');
        $pageSize = request()->input('pageSize', 1000);

        $path = request()->input('path', '');
        $path = str_replace('\\', '/', $path);
        $path = str_replace('../', '/', $path);

        $nowUser = contextGet('nowUser');
        //系统后台超级管理员才有权限查看
        if ($nowUser->level === 99) {
            $realPath = realpath(RUNTIME_BASE_PATH . '/' . $path);
            if (! $realPath) {
                return ApiHelper::genErrorData('文件系统中没找到该文件');
            }
            $result = FileHelper::getContent($realPath);

            //如果有分页
            if ($page) {
                $resultArr = explode(PHP_EOL, $result);
                $result = array_slice($resultArr, (int) ($page - 1) * $pageSize, (int) $pageSize);
                return ApiHelper::genSuccessData(['result' => $result, 'length' => count($result)], '获取成功');
            }

            return ApiHelper::genSuccessData(['result' => $result], '获取成功');
        }

        return ApiHelper::genErrorData('暂无权限查看', 4001);
    }

    /**
     * 查看日志版本.
     */
    #[GetMapping(path: '/system/getLogVersion')]
    public function getLogVersion(): string
    {
        return '62f7111c77a90';
    }
}

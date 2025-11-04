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

use Bailing\Annotation\RateWait;
use Hyperf\Codec\Json;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Swoole\Coroutine\System;

class RateWaitAspect extends AbstractAspect
{
    public array $classes = [];

    public array $annotations = [
        RateWait::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $className = $proceedingJoinPoint->className;
        $methodName = $proceedingJoinPoint->methodName;
        $arguments = $proceedingJoinPoint->arguments['keys'];

        $metadata = $proceedingJoinPoint->getAnnotationMetadata();

        // 获取限流时间
        $waitTimeout = 30;
        $waitTime = 0.2;
        $rateKey = '';
        foreach ($metadata->method as $annotation) {
            if ($annotation instanceof RateWait) {
                $waitTimeout = $annotation->waitTimeout;
                $rateKey = $annotation->rateKey;
                $waitTime = $annotation->waitTime;
                break;
            }
        }

        $cacheName = 'rate_wait:' . str_replace('\\', '_', $className) . ':' . $methodName;
        $rateKeyArr = explode(',', $rateKey);
        foreach ($rateKeyArr as $item) {
            $item = trim($item);
            if (isset($arguments[$item])) {
                if (! is_array($arguments[$item])) {
                    $cacheName.= ':' . $arguments[$item];
                } else {
                    $cacheName.= ':' . md5(Json::encode($arguments[$item]));
                }
            } else {
                $cacheName.= ':' . $item;
            }
        }
        $redis = redis();
        while (true) {
            // 写缓存成功，则跳出循环
            if ($redis->set(trim($cacheName, ':'), 'rate', ['NX', 'EX' => $waitTimeout])) {
                break;
            }
            System::sleep($waitTime);
        }

        $result = $proceedingJoinPoint->process();

        //删redis缓存
        $redis->del($cacheName);

        return $result;
    }
}
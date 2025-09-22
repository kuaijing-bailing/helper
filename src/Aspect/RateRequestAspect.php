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

use Bailing\Annotation\RateRequest;
use Bailing\Constants\Code\Common\CommonCode;
use Bailing\Helper\ApiHelper;
use Bailing\Helper\RequestHelper;
use Hyperf\Codec\Json;
use Hyperf\Context\Context;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;

class RateRequestAspect extends AbstractAspect
{
    public array $classes = [];

    public array $annotations = [
        RateRequest::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();

        // 获取限流时间
        $waitTimeout = 6;
        $rateKey = '';
        foreach ($metadata->method as $key => $annotation) {
            if ($annotation instanceof RateRequest) {
                $waitTimeout = $annotation->waitTimeout;
                $rateKey = $annotation->rateKey;
                break;
            }
        }

        $classMethod = explode(':', RequestHelper::getAdminModule());

        // 如果 rateKey 为空，
        if (empty($rateKey)) {
            $handleArr = request()->all();
        } else {
            $keyArr = explode(',', $rateKey);
            foreach ($keyArr as $item) {
                $handleArr[$item] = request()->input($item, '') ?: $item;
            }
        }

        // 如果为空，认定为raw请求
        if (empty($handleArr)) {
            $nowUser = contextGet('nowUser');
            if (! empty($nowUser)) {
                $handleArr = [
                    'nowUser' => (array) $nowUser,
                ];
            } else {
                return CommonCode::RATE_REQUEST_PARAMS_EMPTY->genI18nMsg(returnNowLang: true);
            }
        }

        $handleArr['class'] = $classMethod[0];
        $handleArr['method'] = $classMethod[1];

        $redis = redis();
        $strKey = 'rate_request:' . md5(serialize($handleArr));
        $result = $redis->set($strKey, Json::encode($handleArr), ['NX', 'EX' => $waitTimeout]);
        if (empty($result)) {
            stdLog()->warning('RateRequestAspect', $handleArr);
            return CommonCode::RATE_REQUEST_EXECUTING->genI18nMsg(returnNowLang: true);
        }

        $result = $proceedingJoinPoint->process();

        //删redis缓存
        $redis->del($strKey);

        return $result;
    }
}

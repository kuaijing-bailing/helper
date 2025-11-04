<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_METHOD)]
class RateWait extends AbstractAnnotation
{
    /**
     * @var string 限流的key，从input中获取，获取不到，则当做值。多个key用逗号隔开。如果为空，则为所有input参数。
     */
    public string $rateKey = '';

    /**
     * @var float|int 轮询等待时间，越短越快，但是越占服务器资源。
     */
    public float|int $waitTime = 0.2;

    /**
     * @var int 超时时间.默认为30秒，建议最高600。不然程序出错清卡下，用户都很久没法操作。
     */
    public int $waitTimeout = 30;

    public function __construct(
        float|int $waitTime = 0.2,
        int $waitTimeout = 30,
        string $rateKey = '',
    ) {
        $this->waitTime = $waitTime;
        $this->waitTimeout = $waitTimeout;
        $this->rateKey = $rateKey;
    }
}

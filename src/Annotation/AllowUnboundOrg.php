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

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * 允许已登录但尚未加入机构的用户访问入驻接口，不授予机构业务权限。
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class AllowUnboundOrg extends AbstractAnnotation {}

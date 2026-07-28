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

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class OrgOrderPermission extends AbstractAnnotation
{
    public string $class = '';

    public string $module = '';

    public function __construct(
        string $class = '',
        string $module = '',
    ) {
        $this->class = $class;
        $this->module = $module;
    }
}

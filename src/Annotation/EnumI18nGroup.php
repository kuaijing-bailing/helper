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

/**
 * 文本国际化类标识.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class EnumI18nGroup extends AbstractAnnotation
{
    // 文本集合名称
    public string $groupCode;

    // 集合描述
    public string  $info;

    /**
     * @param string $groupCode 文本集合名称
     * @param string $info 错误类的描述
     */
    public function __construct(
        string $groupCode,
        string $info,
    ) {
        $this->groupCode = $groupCode;
        $this->info = $info;
    }
}

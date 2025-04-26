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
 * 字典数据表.
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class DictData extends AbstractAnnotation
{
    // 字典标签
    public string $label = '';

    // 字典排序
    public int $sort = 0;

    // 状态（1正常 0停用）
    public int $status = 1;

    // 颜色类型（default、primary、success、info、warning、danger）
    public string $colorType = '';

    // css样式
    public string $cssClass = '';

    // 备注
    public string $remark = '';

    /**
     * @param string $label 字典标签
     * @param int $sort 字典排序
     * @param int $status 状态（1正常 0停用）
     * @param string $colorType 颜色类型（default、primary、success、info、warning、danger）
     * @param string $cssClass css样式
     * @param string $remark 备注
     */
    public function __construct(
        string $label,
        int $sort = 0,
        int $status = 1,
        string $colorType = '',
        string $cssClass = '',
        string $remark = '',
    ) {
        $this->label = $label;
        $this->sort = $sort;
        $this->status = $status;
        $this->colorType = $colorType;
        $this->cssClass = $cssClass;
        $this->remark = $remark;
    }
}

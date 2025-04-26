<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\Office\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * excel导入导出元数据。
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ExcelProperty extends AbstractAnnotation
{
    /**
     * 列表头名称.
     */
    public string $value;

    /**
     * 列顺序.
     */
    public int $index;

    /**
     * 宽度.
     */
    public int $width;

    /**
     * 高度（仅取第一个的）.
     */
    public int $height;

    /**
     * 对齐方式，默认居左.
     */
    public string $align;

    /**
     * 列表头字体颜色.
     */
    public string|int $headColor;

    /**
     * 列表头背景颜色.
     */
    public string|int $headBgColor;

    /**
     * 列表头的高度（仅取第一个的）.
     */
    public int $headHeight;

    /**
     * 列表体字体颜色.
     */
    public string|int $color;

    /**
     * 列表体背景颜色.
     */
    public string|int $bgColor;

    /**
     * 字典数据列表.
     */
    public ?array $dictData = null;

    /**
     * 字典名称.
     */
    public string $dictName;

    /**
     * 数据路径 用法: object.value.
     */
    public string $path;

    public function __construct(
        string $value,
        int $index,
        int $width = null,
        int $height = null,
        string $align = null,
        string|int $headColor = null,
        string|int $headBgColor = null,
        int $headHeight = null,
        string|int $color = null,
        string|int $bgColor = null,
        array $dictData = null,
        string $dictName = null,
        string $path = null,
    ) {
        $this->value = $value;
        $this->index = $index;
        isset($width) && $this->width = $width;
        isset($height) && $this->height = $height;
        isset($align) && $this->align = $align;
        isset($headColor) && $this->headColor = $headColor;
        isset($headBgColor) && $this->headBgColor = $headBgColor;
        isset($headHeight) && $this->headHeight = $headHeight;
        isset($color) && $this->color = $color;
        isset($bgColor) && $this->bgColor = $bgColor;
        isset($dictData) && $this->dictData = $dictData;
        isset($dictName) && $this->dictName = $dictName;
        isset($path) && $this->path = $path;
    }
}

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

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * excel导入导出元数据。
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ExcelProperty extends AbstractAnnotation
{
    /**
     * 列表头名称.
     */
    public string $value;

    /**
     * 列表头名称（国际化）.
     */
    public array $i18nValue;

    /**
     * 示例数据.
     */
    public string $demo;

    /**
     * 示例数据（国际化）.
     */
    public array $i18nDemo;

    /**
     * 字段提示.
     */
    public string $tip;

    /**
     * 字段提示（国际化）.
     */
    public array $i18nTip;

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
     * 列表头是否必填.
     */
    public bool $required;

    /**
     * 列表头的高度（仅取第一个的）.
     */
    public int $headHeight;

    /**
     * 列表体字体颜色.
     */
    public int|string $color;

    /**
     * 列表体背景颜色.
     */
    public int|string $bgColor;

    /**
     * 字典数组（例如公司列表等情况使用）.
     */
    public array $dictData;

    /**
     * 字典名单（将自动前往org服务查找字典内容，自行引入org服务）.
     */
    public string $dictName;

    /**
     * 数据路径 用法: object.value.
     */
    public string $path;

    /**
     * 是否是日期时间格式，日期填写date，时间填写time，日期时间填写dateTime
     */
    public string $dateTime;

    /**
     * @param string $value 列表头名称.
     * @param int $index 列顺序.
     * @param string $demo 示例数据.
     * @param string $tip 字段提示.
     * @param array $i18nValue 列表头名称（国际化）.
     * @param array $i18nDemo 示例数据（国际化）.
     * @param array $i18nTip 字段提示（国际化）.
     * @param int|null $width 宽度.
     * @param int|null $height 高度（仅取第一个的）.
     * @param string|null $align 对齐方式，默认居左.
     * @param bool $required 列表头是否必填.
     * @param int|null $headHeight 列表头的高度（仅取第一个的）.
     * @param int|string|null $color 列表体字体颜色.
     * @param int|string|null $bgColor 列表体背景颜色.
     * @param string $dictName 字典名单（将自动前往org服务查找字典内容，自行引入org服务）.
     * @param array $dictData 字典数组（例如公司列表等情况使用）.
     * @param string|null $path 数据路径 用法: object.value.
     * @param string $dateTime 是否是日期时间格式，日期填写date，时间填写time，日期时间填写dateTime
     */
    public function __construct(
        string $value,
        int $index,
        string $demo = '',
        string $tip = '',
        array $i18nValue = [],
        array $i18nDemo = [],
        array $i18nTip = [],
        int $width = null,
        int $height = null,
        string $align = null,
        bool $required = false,
        int $headHeight = null,
        int|string $color = null,
        int|string $bgColor = null,
        string $dictName = '',
        array $dictData = [],
        string $path = null,
        string $dateTime = '',
    ) {
        $this->value = $value;
        $this->index = $index;
        $this->required = $required;
        isset($demo) && $this->demo = $demo;
        isset($tip) && $this->tip = $tip;
        isset($i18nValue) && $this->i18nValue = $i18nValue;
        isset($i18nDemo) && $this->i18nDemo = $i18nDemo;
        isset($i18nTip) && $this->i18nTip = $i18nTip;
        isset($width) && $this->width = $width;
        isset($height) && $this->height = $height;
        isset($align) && $this->align = $align;
        isset($headHeight) && $this->headHeight = $headHeight;
        isset($color) && $this->color = $color;
        isset($bgColor) && $this->bgColor = $bgColor;
        isset($dictName) && $this->dictName = $dictName;
        isset($dictData) && $this->dictData = $dictData;
        isset($path) && $this->path = $path;
        isset($dateTime) && $this->dateTime = $dateTime;
    }
}

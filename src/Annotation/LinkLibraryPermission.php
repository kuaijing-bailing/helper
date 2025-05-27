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

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class LinkLibraryPermission extends AbstractAnnotation
{
    public string $cat = ''; // 一级分类，例如 基础basic，财务 bill，

    public string $port = ''; // 端，用户端user，机构端org

    public string $name = ''; // 名称，

    public ?array  $i18nName = null; // 国际化名称，i18n格式 {en: 'xxxxx', zh_hk: 'xxxxxx', zh_tw: 'xxxxxx', ja: 'xxxxxx'}

    public string $alias = ''; // 链接别名，不填不能选

    public string $link = ''; // 移动端具体链接，不含域名，不填不能选

    public string $pc_link = ''; // PC端具体链接，不含域名，不填不能选

    public array $sub = []; // [['name' => '分类列表', 'label' => 'category']]

    public string $show = '1'; // 是否展示在功能库

    public string $sort = '0'; // 排序，越大越前，一级菜单千进位，二级菜单百进位，默认0

    public string $micro = ''; // 服务提供者，重新上报时会清空

    public string $icon = ''; // 链接库按钮icon

    public function __construct(
        string $cat = '',
        string $port = '',
        string $name = '',
        ?array $i18nName = null,
        string $alias = '',
        string $link = '',
        string $pc_link = '',
        array $sub = [],
        string $show = '1',
        string $sort = '0',
        string $micro = '',
        string $icon = ''
    ) {
        $this->cat = $cat;
        $this->port = $port;
        $this->name = $name;
        $this->i18nName = $i18nName;
        $this->alias = $alias;
        $this->link = $link;
        $this->pc_link = $pc_link;
        $this->sub = $sub;
        $this->show = $show;
        $this->sort = $sort;
        $this->micro = $micro;
        $this->icon = $icon;
    }
}

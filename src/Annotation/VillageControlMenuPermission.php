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
class VillageControlMenuPermission extends AbstractAnnotation
{
    /**
     * @param string $name 菜单名称
     * @param null|string $icon 图标，http开头即认定为图片
     * @param null|string $active_icon 选中后图标，http开头即认定为图片
     * @param null|string $menu_type 按钮集合类型，暂定 village、build、room、villageUser
     * @param null|string $alias 路由别名，http开头即认定为iframe，url匹配{orgId},{villageId},{buildId},{roomId},{userId}参数
     * @param string $app 前端服务
     * @param null|int $sort 排序，越大越前
     */
    public function __construct(
        public string $name,
        public ?string $icon = null,
        public ?string $active_icon = null,
        public ?string $menu_type = 'room',
        public ?string $alias = null,
        public string $app = '',
        public ?int $sort = 0
    ) {
    }
}

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
class OrgPermission extends AbstractAnnotation
{
    public string $module = ''; // 管理后台:系统设置:角色管理

    public string $action = ''; // 查看、增加、修改、删除

    public string $icon = ''; // 图标

    public string $activeIcon = ''; // 选中后图标

    public int $menuType = 1; // 1展示，0归类

    public string $urlType = 'path'; // 	URL类别(path, frame_url, target_url)

    public string $alias = ''; // 路由别名

    public string $url = ''; // 菜单链接

    public int $keepAlive = 1; // 前端是否缓存

    public int $sort = 0; // 排序，越大越前，一级菜单千进位，二级菜单百进位，默认0

    public int $status = 1; // 状态，0和1，默认1

    public string $type = ''; // 类型，label作为小标识，无法点击

    public int $appId = 0; // 是否关联应用，填写 appid 表的自增ID

    public string $app = ''; // 微前端提供者

    public int $orgId = 0; // 固定的自增ID

    public int $parentOrgId = 0; // 固定的父自增ID

    public function __construct(
        string $module = '',
        string $action = '',
        string $icon = '',
        string $activeIcon = '',
        int $menuType = 1,
        string $urlType = 'path',
        string $alias = '',
        string $url = '',
        int $keepAlive = 1,
        int $sort = 0,
        int $status = 1,
        string $type = '',
        int $appId = 0,
        string $app = '',
        int $orgId = 0,
        int $parentOrgId = 0,
    ) {
        $this->module = $module;
        $this->action = $action;
        $this->icon = $icon;
        $this->activeIcon = $activeIcon;
        $this->menuType = $menuType;
        $this->urlType = $urlType;
        $this->alias = $alias;
        $this->url = $url;
        $this->keepAlive = $keepAlive;
        $this->sort = $sort;
        $this->status = $status;
        $this->type = $type;
        $this->appId = $appId;
        $this->app = $app;
        $this->orgId = $orgId;
        $this->parentOrgId = $parentOrgId;
    }
}

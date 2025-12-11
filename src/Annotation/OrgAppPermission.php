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

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class OrgAppPermission extends AbstractAnnotation
{
    public string $appAlias = ''; // 应用的别名

    public string $module = ''; // 管理后台:系统设置:角色管理，不填则与orgPermissionHelper的module一致

    public bool $pcShow = true; // 是否在PC端显示

    public bool $mobileShow = true; // 是否在移动端显示

    public int $sort = 0; // 排序值，从大到小，保持按照OrgPermission的排序规则一致

    public function __construct(
        string $appAlias = '',
        string $module = '',
        bool $pcShow = true,
        bool $mobileShow = true,
        int $sort = 0
    ) {
        $this->appAlias = $appAlias;
        $this->module = $module;
        $this->pcShow = $pcShow;
        $this->mobileShow = $mobileShow;
        $this->sort = $sort;
    }
}

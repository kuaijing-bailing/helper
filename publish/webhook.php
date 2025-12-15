<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */

return [
    'webhook_list' => [
        // 服务注册webhook，alias会自动增加服务名为前缀，不要手动添加。
        // ['name' => '机构员工新增', 'alias' => 'user.add'],
    ],
    'webhook_node_list' => [
        // 服务注册webhook的node节点，监听其他服务的webhook调用。
        // ['webhook_url' => '/helloWord', 'webhook_alias' => 'org.user.add'],
    ],
];

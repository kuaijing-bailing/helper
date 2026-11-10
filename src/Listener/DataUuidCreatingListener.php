<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */

namespace Bailing\Listener;

use Bailing\Helper\StrHelper;
use Bailing\Trait\HasDataUuid;
use Hyperf\Database\Model\Events\Creating;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

#[Listener]
class DataUuidCreatingListener implements ListenerInterface
{
    /** 获取需要监听的数据库保存事件。 */
    public function listen(): array
    {
        return [
            Creating::class,
        ];
    }

    /**
     * 新增使用 HasDataUuid trait 的模型时生成 data_uuid。
     * @param object $event 数据库模型创建事件
     */
    public function process(object $event): void
    {
        $model = $event->getModel();

        if (! array_key_exists(HasDataUuid::class, class_uses($model))) {
            return;
        }

        $model->data_uuid = StrHelper::getUUID();
    }
}

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

use Bailing\Trait\DbModifyLog;
use Hyperf\Database\Model\Events\Saving;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

#[Listener]
class DbSavingListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            Saving::class,
        ];
    }

    public function process(object $event): void
    {
        $model = $event->getModel();
        if (! array_key_exists(DbModifyLog::class, class_uses($model))) {
            return;
        }

        $nowUser = contextGet('nowUser');
        if (empty($nowUser)) {
            return;
        }

        if (empty($model->id)) {
            $model->created_uid = $nowUser->id;
            $model->created_name = $nowUser->name ?? '';
        } else {
            $model->updated_uid = $nowUser->id;
            $model->updated_name = $nowUser->name ?? '';
        }
    }
}

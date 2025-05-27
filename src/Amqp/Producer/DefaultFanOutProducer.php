<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing\Amqp\Producer;

use Hyperf\Amqp\Annotation\Producer;
use Hyperf\Amqp\Message\ProducerMessage;
use Hyperf\Amqp\Message\Type;

#[Producer(exchange: 'system.defaultFanOut', routingKey: 'system.defaultFanOut')]
class DefaultFanOutProducer extends ProducerMessage
{
    protected Type|string $type = Type::FANOUT; // 广播消息

    public function __construct(array $data)
    {
        stdLog()->debug('defaultFanOut', $data);

        // 没有消费类，直接报错
        if (empty($data['type'])) {
            throw new \Exception('type参数 未传递');
        }
        if (empty($data['data'])) {
            throw new \Exception('data参数 未传递');
        }

        $this->payload = $data;
    }
}

<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */

namespace Bailing\Amqp\Message;

use Hyperf\Amqp\Builder\ExchangeBuilder;
use Hyperf\Amqp\Message\ProducerMessageInterface;
use Hyperf\Amqp\Message\Type;
use PhpAmqpLib\Wire\AMQPTable;

final class LocaleAwareProducerMessage implements ProducerMessageInterface
{
    public const LOCALE_HEADER = 'x-bailing-locale';

    /** @var array<string, mixed> */
    private array $properties;

    public function __construct(private readonly ProducerMessageInterface $message, string $locale)
    {
        $this->properties = $message->getProperties();
        $headers = $this->properties['application_headers'] ?? null;
        if ($headers instanceof AMQPTable) {
            $headers = clone $headers;
        } else {
            $headers = new AMQPTable(is_array($headers) ? $headers : []);
        }
        $headers->set(self::LOCALE_HEADER, $locale);
        $this->properties['application_headers'] = $headers;
    }

    public function setPayload(mixed $data): static
    {
        $this->message->setPayload($data);
        return $this;
    }

    public function payload(): string
    {
        return $this->message->payload();
    }

    /** @return array<string, mixed> */
    public function getProperties(): array
    {
        return $this->properties;
    }

    public function setPoolName(string $name): static
    {
        $this->message->setPoolName($name);
        return $this;
    }

    public function getPoolName(): string
    {
        return $this->message->getPoolName();
    }

    public function setType(string|Type $type): static
    {
        $this->message->setType($type);
        return $this;
    }

    public function getType(): string|Type
    {
        return $this->message->getType();
    }

    public function setExchange(string $exchange): static
    {
        $this->message->setExchange($exchange);
        return $this;
    }

    public function getExchange(): string
    {
        return $this->message->getExchange();
    }

    /** @param array<int|string, string>|string $routingKey */
    public function setRoutingKey(array|string $routingKey): static
    {
        $this->message->setRoutingKey($routingKey);
        return $this;
    }

    /** @return array<int|string, string>|string */
    public function getRoutingKey(): array|string
    {
        return $this->message->getRoutingKey();
    }

    public function getExchangeBuilder(): ExchangeBuilder
    {
        return $this->message->getExchangeBuilder();
    }

    public function serialize(): string
    {
        return $this->message->serialize();
    }

    public function unserialize(string $data): mixed
    {
        return $this->message->unserialize($data);
    }
}

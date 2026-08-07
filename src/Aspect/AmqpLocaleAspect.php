<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */

namespace Bailing\Aspect;

use Bailing\Amqp\Message\LocaleAwareProducerMessage;
use Hyperf\Amqp\Annotation\Consumer as ConsumerAnnotation;
use Hyperf\Amqp\Annotation\Producer as ProducerAnnotation;
use Hyperf\Amqp\Message\ProducerMessageInterface;
use Hyperf\Amqp\Producer;
use Hyperf\Contract\TranslatorInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

#[Aspect]
class AmqpLocaleAspect extends AbstractAspect
{
    /** @var string[] */
    public array $classes = [
        Producer::class . '::produce',
    ];

    /** @var string[] */
    public array $annotations = [
        ConsumerAnnotation::class,
    ];

    public function __construct(private readonly TranslatorInterface $translator) {}

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if ($proceedingJoinPoint->className === Producer::class && $proceedingJoinPoint->methodName === 'produce') {
            $this->attachLocale($proceedingJoinPoint);
        } elseif ($proceedingJoinPoint->methodName === 'consumeMessage') {
            $this->restoreLocale($proceedingJoinPoint);
        }

        return $proceedingJoinPoint->process();
    }

    private function attachLocale(ProceedingJoinPoint $proceedingJoinPoint): void
    {
        $producerMessage = $proceedingJoinPoint->arguments['keys']['producerMessage'] ?? null;
        if (! $producerMessage instanceof ProducerMessageInterface || $producerMessage instanceof LocaleAwareProducerMessage) {
            return;
        }

        $locale = $this->translator->getLocale();
        if ($locale === '') {
            return;
        }

        $this->injectProducerAnnotation($producerMessage);
        $proceedingJoinPoint->arguments['keys']['producerMessage'] = new LocaleAwareProducerMessage($producerMessage, $locale);
    }

    private function restoreLocale(ProceedingJoinPoint $proceedingJoinPoint): void
    {
        $arguments = $proceedingJoinPoint->getArguments();
        $message = $arguments[1] ?? null;
        if (! $message instanceof AMQPMessage || ! $message->has('application_headers')) {
            return;
        }

        $headers = $message->get('application_headers');
        if ($headers instanceof AMQPTable) {
            $headers = $headers->getNativeData();
        }
        $locale = is_array($headers) ? ($headers[LocaleAwareProducerMessage::LOCALE_HEADER] ?? '') : '';
        if (is_string($locale) && $locale !== '') {
            $this->translator->setLocale($locale);
        }
    }

    private function injectProducerAnnotation(ProducerMessageInterface $producerMessage): void
    {
        /** @var null|ProducerAnnotation $annotation */
        $annotation = AnnotationCollector::getClassAnnotation($producerMessage::class, ProducerAnnotation::class);
        if ($annotation === null) {
            return;
        }

        $annotation->routingKey && $producerMessage->setRoutingKey($annotation->routingKey);
        $annotation->exchange && $producerMessage->setExchange($annotation->exchange);
        $annotation->pool && $producerMessage->setPoolName($annotation->pool);
    }
}

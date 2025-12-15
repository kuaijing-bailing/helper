<?php

declare(strict_types=1);
/**
 * This file is part of Kuaijing Bailing.
 *
 * @link     https://www.kuaijingai.com
 * @document https://help.kuaijingai.com
 * @contact  www.kuaijingai.com 7*12 9:00-21:00
 */
namespace Bailing;

use Bailing\Aspect\RateRequestAspect;
use Bailing\Aspect\RateWaitAspect;
use Bailing\Middleware\TranslationMiddleware;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'aspects' => [
                RateRequestAspect::class,
                RateWaitAspect::class,
            ],
            'dependencies' => [
                \Bailing\IotCloud\HikCloud\Application::class => \Bailing\IotCloud\HikCloud\ApplicationFactory::class,
                \Bailing\IotCloud\YunRui\Application::class => \Bailing\IotCloud\YunRui\ApplicationFactory::class,
                \Bailing\IotCloud\Ys7\Application::class => \Bailing\IotCloud\Ys7\ApplicationFactory::class,
            ],
            'listeners' => [
            ],
            'commands' => [
            ],
            'middlewares' => [
                'http' => [
                    TranslationMiddleware::class,
                ],
                'jsonrpc-http' => [
                    TranslationMiddleware::class,
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for webhook.',
                    'source' => __DIR__ . '/../publish/webhook.php',
                    'destination' => BASE_PATH . '/config/autoload/webhook.php',
                ],
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
        ];
    }
}

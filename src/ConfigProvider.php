<?php

declare(strict_types=1);

namespace HyperfLog;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config file for log viewer.',
                    'source' => __DIR__ . '/../publish/log_viewer.php',
                    'destination' => BASE_PATH . '/config/autoload/log_viewer.php',
                ],
            ],
        ];
    }
}

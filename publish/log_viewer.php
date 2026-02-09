<?php

declare(strict_types=1);

return [
    // 日志文件目录
    'log_path' => BASE_PATH . '/runtime/logs',

    // 缓存目录
    'cache_path' => BASE_PATH . '/runtime/cache/log_viewer',

    // 日志类型配置文件存储路径
    'config_path' => BASE_PATH . '/runtime/log-viewer-types.json',

    // 每页显示条数
    'per_page' => 50,

    // 缓存有效期（秒），今天的日志用此值，历史日志用 10 分钟
    'cache_expiry' => 60,
];

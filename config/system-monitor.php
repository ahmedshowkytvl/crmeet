<?php

return [
    /*
    |--------------------------------------------------------------------------
    | System Monitor Configuration
    |--------------------------------------------------------------------------
    |
    | إعدادات نظام مراقبة النظام
    |
    */

    'enabled' => env('SYSTEM_MONITOR_ENABLED', true),
    
    'refresh_interval' => env('SYSTEM_MONITOR_REFRESH_INTERVAL', 5000), // milliseconds
    
    'websocket' => [
        'enabled' => env('SYSTEM_MONITOR_WEBSOCKET_ENABLED', true),
        'port' => env('SYSTEM_MONITOR_WEBSOCKET_PORT', 8080),
        'host' => env('SYSTEM_MONITOR_WEBSOCKET_HOST', '0.0.0.0'),
    ],
    
    'alerts' => [
        'memory_threshold' => env('SYSTEM_MONITOR_MEMORY_THRESHOLD', 90), // percentage
        'disk_threshold' => env('SYSTEM_MONITOR_DISK_THRESHOLD', 90), // percentage
        'response_time_threshold' => env('SYSTEM_MONITOR_RESPONSE_THRESHOLD', 2000), // milliseconds
        'cpu_threshold' => env('SYSTEM_MONITOR_CPU_THRESHOLD', 80), // percentage
    ],
    
    'security' => [
        'allowed_ips' => env('SYSTEM_MONITOR_ALLOWED_IPS', '192.168.0.0/16,10.0.0.0/8,172.16.0.0/12'),
        'require_auth' => env('SYSTEM_MONITOR_REQUIRE_AUTH', false),
        'api_key' => env('SYSTEM_MONITOR_API_KEY', null),
    ],
    
    'features' => [
        'show_server_info' => env('SYSTEM_MONITOR_SHOW_SERVER_INFO', true),
        'show_database_info' => env('SYSTEM_MONITOR_SHOW_DATABASE_INFO', true),
        'show_performance_metrics' => env('SYSTEM_MONITOR_SHOW_PERFORMANCE', true),
        'show_active_users' => env('SYSTEM_MONITOR_SHOW_ACTIVE_USERS', true),
        'show_recent_activities' => env('SYSTEM_MONITOR_SHOW_ACTIVITIES', true),
        'show_charts' => env('SYSTEM_MONITOR_SHOW_CHARTS', true),
    ],
    
    'charts' => [
        'max_data_points' => env('SYSTEM_MONITOR_MAX_DATA_POINTS', 20),
        'update_interval' => env('SYSTEM_MONITOR_CHART_UPDATE_INTERVAL', 5000), // milliseconds
    ],
    
    'database' => [
        'slow_query_threshold' => env('SYSTEM_MONITOR_SLOW_QUERY_THRESHOLD', 1000), // milliseconds
        'max_connections_warning' => env('SYSTEM_MONITOR_MAX_CONNECTIONS_WARNING', 80), // percentage
    ],
    
    'cache' => [
        'driver' => env('SYSTEM_MONITOR_CACHE_DRIVER', 'file'),
        'ttl' => env('SYSTEM_MONITOR_CACHE_TTL', 60), // seconds
    ],
    
    'logging' => [
        'enabled' => env('SYSTEM_MONITOR_LOGGING_ENABLED', true),
        'level' => env('SYSTEM_MONITOR_LOG_LEVEL', 'info'),
        'channel' => env('SYSTEM_MONITOR_LOG_CHANNEL', 'single'),
    ],
    
    'notifications' => [
        'email' => [
            'enabled' => env('SYSTEM_MONITOR_EMAIL_NOTIFICATIONS', false),
            'recipients' => env('SYSTEM_MONITOR_EMAIL_RECIPIENTS', ''),
        ],
        'slack' => [
            'enabled' => env('SYSTEM_MONITOR_SLACK_NOTIFICATIONS', false),
            'webhook_url' => env('SYSTEM_MONITOR_SLACK_WEBHOOK', ''),
        ],
    ],
    
    'ui' => [
        'theme' => env('SYSTEM_MONITOR_THEME', 'default'),
        'language' => env('SYSTEM_MONITOR_LANGUAGE', 'ar'),
        'timezone' => env('SYSTEM_MONITOR_TIMEZONE', 'Asia/Riyadh'),
        'auto_refresh' => env('SYSTEM_MONITOR_AUTO_REFRESH', true),
    ],
];







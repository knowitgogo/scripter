<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Redis
    |--------------------------------------------------------------------------
    |
    | When enabled, readiness checks include Redis connectivity and Redis-backed
    | drivers become available for cache and queue failover chains.
    |
    */

    'redis' => [
        'enabled' => env('REDIS_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    */

    'cache' => [
        'store' => env('CACHE_STORE', 'database'),
        'redis_store' => env('CACHE_REDIS_STORE', 'redis'),

        'patterns' => [
            'widget_config' => 'widget:config:{website_widget_uuid}',
            'widget_catalog' => 'widget:catalog:published',
            'user_permissions' => 'user:permissions:{user_uuid}',
            'analytics_dashboard' => 'analytics:dashboard:{website_uuid}:{period}',
            'plan_limits' => 'plan:limits:{user_uuid}',
        ],

        'ttl' => [
            'widget_config' => (int) env('CACHE_TTL_WIDGET_CONFIG', 900),
            'widget_catalog' => (int) env('CACHE_TTL_WIDGET_CATALOG', 3600),
            'user_permissions' => (int) env('CACHE_TTL_USER_PERMISSIONS', 1800),
            'analytics_dashboard' => (int) env('CACHE_TTL_ANALYTICS_DASHBOARD', 300),
            'plan_limits' => (int) env('CACHE_TTL_PLAN_LIMITS', 3600),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    */

    'queue' => [
        'connection' => env('QUEUE_CONNECTION', 'database'),
        'redis_connection' => env('QUEUE_REDIS_CONNECTION', 'redis'),

        'names' => [
            'default' => env('QUEUE_NAME_DEFAULT', 'default'),
            'analytics' => env('QUEUE_NAME_ANALYTICS', 'analytics'),
            'billing' => env('QUEUE_NAME_BILLING', 'billing'),
        ],

        'retry_after' => (int) env('QUEUE_RETRY_AFTER', 90),
        'tries' => (int) env('QUEUE_TRIES', 3),
    ],

];

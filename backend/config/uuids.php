<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Public Identifier Column
    |--------------------------------------------------------------------------
    |
    | All externally addressable entities use this column as their public key.
    |
    */

    'column' => 'uuid',

    /*
    |--------------------------------------------------------------------------
    | Public Entities
    |--------------------------------------------------------------------------
    |
    | Database tables that expose a UUID to clients. Internal integer primary
    | keys are never returned in API responses.
    |
    | Excluded by design:
    | - widget_keys (credential is the public key)
    | - analytics_events (high-volume, not externally addressable)
    |
    */

    'entities' => [
        'users',
        'websites',
        'tags',
        'widgets',
        'widget_categories',
        'widget_versions',
        'website_widgets',
        'plans',
        'subscriptions',
        'payments',
        'audit_logs',
    ],

];

<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    */

    'enabled' => env('AUDIT_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Persistence Mode
    |--------------------------------------------------------------------------
    |
    | When async is true, audit entries are written via PersistAuditLogJob on
    | the default queue. When false, entries are persisted synchronously.
    |
    */

    'async' => env('AUDIT_ASYNC', true),

    'queue' => env('AUDIT_QUEUE', 'default'),

];

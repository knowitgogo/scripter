<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | API Route Prefix
    |--------------------------------------------------------------------------
    |
    | Global prefix applied in bootstrap/app.php via apiPrefix. Versioned
    | routes are registered as /{prefix}/{version}/...
    |
    */

    'prefix' => env('API_PREFIX', 'api'),

    /*
    |--------------------------------------------------------------------------
    | API Versioning
    |--------------------------------------------------------------------------
    |
    | default_version: active version for new clients and system responses.
    | supported_versions: versions with registered route files in routes/api/.
    |
    */

    'default_version' => env('API_DEFAULT_VERSION', 'v1'),

    'supported_versions' => array_map(
        trim(...),
        explode(',', (string) env('API_SUPPORTED_VERSIONS', 'v1')),
    ),

    /*
    |--------------------------------------------------------------------------
    | Version Response Header
    |--------------------------------------------------------------------------
    |
    | Added to all versioned API responses by SetApiVersionHeader middleware.
    |
    */

    'version_header' => env('API_VERSION_HEADER', 'X-API-Version'),

];

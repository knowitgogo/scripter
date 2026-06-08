<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAPI Specification
    |--------------------------------------------------------------------------
    */

    'spec_path' => env('OPENAPI_SPEC_PATH', base_path('openapi/openapi.yaml')),

    'title' => env('OPENAPI_TITLE', 'Script Manager API'),

    'version' => env('OPENAPI_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Documentation Routes
    |--------------------------------------------------------------------------
    |
    | Served under the /api prefix. Spec route returns YAML; docs route
    | renders Swagger UI.
    |
    */

    'routes' => [
        'spec' => env('OPENAPI_SPEC_ROUTE', 'openapi.yaml'),
        'ui' => env('OPENAPI_UI_ROUTE', 'docs'),
    ],

    'ui_enabled' => env('OPENAPI_UI_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Swagger UI
    |--------------------------------------------------------------------------
    */

    'swagger_ui' => [
        'cdn_version' => env('OPENAPI_SWAGGER_UI_VERSION', '5.18.2'),
    ],

];

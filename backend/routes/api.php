<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Unversioned meta routes (OpenAPI, Swagger UI) load first. Versioned routes
| are registered from routes/api/{version}.php per config/api.php.
|
*/

require __DIR__.'/openapi.php';

foreach (config('api.supported_versions', ['v1']) as $version) {
    $routeFile = __DIR__."/api/{$version}.php";

    if (! is_file($routeFile)) {
        continue;
    }

    Route::prefix($version)
        ->middleware('api.version:'.$version)
        ->name("api.{$version}.")
        ->group($routeFile);
}

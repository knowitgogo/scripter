<?php

declare(strict_types=1);

use App\Http\Controllers\Api\OpenApiSpecController;
use App\Http\Controllers\Api\SwaggerUiController;
use Illuminate\Support\Facades\Route;

Route::get(config('openapi.routes.spec'), OpenApiSpecController::class)
    ->name('api.openapi.spec');

Route::get(config('openapi.routes.ui'), SwaggerUiController::class)
    ->name('api.openapi.docs');

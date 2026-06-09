<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\RefreshTokenController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\ReadinessController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 Routes
|--------------------------------------------------------------------------
|
| All versioned domain endpoints register here. Controllers live under
| App\Http\Controllers\Api\V1 and must remain thin.
|
*/

Route::post('auth/login', LoginController::class)->name('auth.login');
Route::post('auth/refresh', RefreshTokenController::class)->name('auth.refresh');
Route::post('auth/logout', LogoutController::class)->middleware('auth:api')->name('auth.logout');

Route::get('health', HealthController::class)->name('health');
Route::get('ready', ReadinessController::class)->name('ready');

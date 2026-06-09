<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Auth\RefreshTokenController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\ReadinessController;
use App\Http\Controllers\Api\V1\Website\DestroyWebsiteController;
use App\Http\Controllers\Api\V1\Website\IndexWebsitesController;
use App\Http\Controllers\Api\V1\Website\ShowWebsiteController;
use App\Http\Controllers\Api\V1\Website\StoreWebsiteController;
use App\Http\Controllers\Api\V1\Website\UpdateWebsiteController;
use App\Enums\Permission;
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

Route::post('auth/register', RegisterController::class)->name('auth.register');
Route::post('auth/login', LoginController::class)->name('auth.login');
Route::post('auth/refresh', RefreshTokenController::class)->name('auth.refresh');
Route::post('auth/logout', LogoutController::class)->middleware('auth:api')->name('auth.logout');

Route::get('me', MeController::class)->middleware('auth:api')->name('me');

Route::middleware(['auth:api', 'permission:'.Permission::WebsitesView->value])->group(function (): void {
    Route::get('websites', IndexWebsitesController::class)->name('websites.index');
    Route::get('websites/{website}', ShowWebsiteController::class)->name('websites.show');
});

Route::middleware(['auth:api', 'permission:'.Permission::WebsitesManage->value])->group(function (): void {
    Route::post('websites', StoreWebsiteController::class)->name('websites.store');
    Route::put('websites/{website}', UpdateWebsiteController::class)->name('websites.update');
    Route::delete('websites/{website}', DestroyWebsiteController::class)->name('websites.destroy');
});

Route::get('health', HealthController::class)->name('health');
Route::get('ready', ReadinessController::class)->name('ready');

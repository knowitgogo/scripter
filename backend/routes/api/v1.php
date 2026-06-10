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
use App\Http\Controllers\Api\V1\Tag\DestroyTagController;
use App\Http\Controllers\Api\V1\Tag\IndexTagsController;
use App\Http\Controllers\Api\V1\Tag\ShowTagController;
use App\Http\Controllers\Api\V1\Tag\StoreTagController;
use App\Http\Controllers\Api\V1\Tag\UpdateTagController;
use App\Http\Controllers\Api\V1\Widget\ActivateWidgetController;
use App\Http\Controllers\Api\V1\Widget\DeactivateWidgetController;
use App\Http\Controllers\Api\V1\Widget\DeprecateWidgetVersionController;
use App\Http\Controllers\Api\V1\Widget\PublishWidgetVersionController;
use App\Http\Controllers\Api\V1\Widget\RollbackWidgetVersionController;
use App\Http\Controllers\Api\V1\Widget\IndexWidgetsController;
use App\Http\Controllers\Api\V1\Widget\RegisterWidgetController;
use App\Http\Controllers\Api\V1\Widget\StoreWebsiteWidgetController;
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

Route::middleware(['auth:api', 'permission:'.Permission::TagsView->value])->group(function (): void {
    Route::get('tags', IndexTagsController::class)->name('tags.index');
    Route::get('tags/{tag}', ShowTagController::class)->name('tags.show');
});

Route::middleware(['auth:api', 'permission:'.Permission::TagsManage->value])->group(function (): void {
    Route::post('tags', StoreTagController::class)->name('tags.store');
    Route::put('tags/{tag}', UpdateTagController::class)->name('tags.update');
    Route::delete('tags/{tag}', DestroyTagController::class)->name('tags.destroy');
});

Route::middleware(['auth:api', 'permission:'.Permission::WidgetsView->value])->group(function (): void {
    Route::get('widgets', IndexWidgetsController::class)->name('widgets.index');
});

Route::middleware(['auth:api', 'permission:'.Permission::WidgetsInstall->value])->group(function (): void {
    Route::post('website-widgets', StoreWebsiteWidgetController::class)->name('website-widgets.store');
});

Route::middleware(['auth:api', 'permission:'.Permission::AdminWidgetsPublish->value])->group(function (): void {
    Route::post('widgets', RegisterWidgetController::class)->name('widgets.register');
    Route::post('widgets/{widget}/activate', ActivateWidgetController::class)->name('widgets.activate');
    Route::post('widgets/{widget}/deactivate', DeactivateWidgetController::class)->name('widgets.deactivate');
    Route::post('widget-versions/{widget_version}/publish', PublishWidgetVersionController::class)->name('widget-versions.publish');
    Route::post('widget-versions/{widget_version}/deprecate', DeprecateWidgetVersionController::class)->name('widget-versions.deprecate');
    Route::post('widget-versions/{widget_version}/rollback', RollbackWidgetVersionController::class)->name('widget-versions.rollback');
});

Route::get('health', HealthController::class)->name('health');
Route::get('ready', ReadinessController::class)->name('ready');

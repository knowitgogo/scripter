<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\InfrastructureProbeRepositoryInterface;
use App\Repositories\Infrastructure\InfrastructureProbeRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Binds repository contracts to Eloquent implementations.
 *
 * Register domain bindings here as modules are implemented, e.g.:
 * $this->app->bind(WebsiteRepositoryInterface::class, WebsiteRepository::class);
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        InfrastructureProbeRepositoryInterface::class => InfrastructureProbeRepository::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}

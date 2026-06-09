<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Audit\EloquentAuditLogRepository;
use App\Repositories\Cache\LaravelCacheRepository;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Repositories\Contracts\CacheRepositoryInterface;
use App\Repositories\Contracts\InfrastructureProbeRepositoryInterface;
use App\Repositories\Contracts\OpenApiSpecRepositoryInterface;
use App\Repositories\Contracts\QueueDispatcherInterface;
use App\Repositories\Infrastructure\InfrastructureProbeRepository;
use App\Repositories\OpenApi\FileOpenApiSpecRepository;
use App\Repositories\Queue\LaravelQueueDispatcher;
use Illuminate\Support\ServiceProvider;

/**
 * Binds repository contracts to concrete implementations.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        InfrastructureProbeRepositoryInterface::class => InfrastructureProbeRepository::class,
        CacheRepositoryInterface::class => LaravelCacheRepository::class,
        QueueDispatcherInterface::class => LaravelQueueDispatcher::class,
        OpenApiSpecRepositoryInterface::class => FileOpenApiSpecRepository::class,
        AuditLogRepositoryInterface::class => EloquentAuditLogRepository::class,
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

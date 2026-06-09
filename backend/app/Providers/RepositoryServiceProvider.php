<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Audit\EloquentAuditLogRepository;
use App\Repositories\Cache\LaravelCacheRepository;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Repositories\Contracts\CacheRepositoryInterface;
use App\Repositories\Contracts\InfrastructureProbeRepositoryInterface;
use App\Repositories\Contracts\OpenApiSpecRepositoryInterface;
use App\Repositories\Contracts\PermissionsRepositoryInterface;
use App\Repositories\Contracts\QueueDispatcherInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\TagRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\WebsiteRepositoryInterface;
use App\Repositories\Contracts\WebsiteTagRepositoryInterface;
use App\Repositories\Eloquent\EloquentRoleRepository;
use App\Repositories\Eloquent\EloquentTagRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Repositories\Eloquent\EloquentWebsiteRepository;
use App\Repositories\Eloquent\EloquentWebsiteTagRepository;
use App\Repositories\Infrastructure\InfrastructureProbeRepository;
use App\Repositories\OpenApi\FileOpenApiSpecRepository;
use App\Repositories\Permissions\ConfigPermissionsRepository;
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
        UserRepositoryInterface::class => EloquentUserRepository::class,
        RoleRepositoryInterface::class => EloquentRoleRepository::class,
        WebsiteRepositoryInterface::class => EloquentWebsiteRepository::class,
        TagRepositoryInterface::class => EloquentTagRepository::class,
        WebsiteTagRepositoryInterface::class => EloquentWebsiteTagRepository::class,
        PermissionsRepositoryInterface::class => ConfigPermissionsRepository::class,
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

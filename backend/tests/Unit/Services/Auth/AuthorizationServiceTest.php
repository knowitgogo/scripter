<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Auth;

use App\Enums\Permission;
use App\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Cache\LaravelCacheRepository;
use App\Repositories\Permissions\ConfigPermissionsRepository;
use App\Services\Auth\AuthorizationService;
use App\Services\Auth\PermissionService;
use App\Services\Infrastructure\CacheService;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class AuthorizationServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthorizationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();

        $this->service = new AuthorizationService(
            new PermissionService(
                new ConfigPermissionsRepository,
                new CacheService(new LaravelCacheRepository),
            ),
        );
    }

    #[Test]
    public function authorize_permission_passes_when_user_has_permission(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Admin->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->service->authorizePermission($user, Permission::AdminUsersView);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function authorize_permission_throws_when_user_is_missing(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Unauthenticated.');

        $this->service->authorizePermission(null, Permission::WebsitesView);
    }

    #[Test]
    public function authorize_permission_throws_when_user_lacks_permission(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('Forbidden.');

        $this->service->authorizePermission($user, Permission::AdminUsersView);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Auth;

use App\Enums\Permission;
use App\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Permissions\ConfigPermissionsRepository;
use App\Services\Auth\PermissionService;
use App\Services\Infrastructure\CacheService;
use App\Repositories\Cache\LaravelCacheRepository;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PermissionServiceTest extends TestCase
{
    use RefreshDatabase;

    private PermissionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();

        $this->service = new PermissionService(
            new ConfigPermissionsRepository,
            new CacheService(new LaravelCacheRepository),
        );
    }

    #[Test]
    public function it_resolves_permissions_for_customer(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $role->id]);

        $dto = $this->service->forUser($user);

        $this->assertSame($user->uuid, $dto->user_uuid);
        $this->assertSame(RoleSlug::Customer, $dto->role);
        $this->assertTrue($dto->allows(Permission::WebsitesView));
        $this->assertFalse($dto->allows(Permission::AdminUsersView));
    }

    #[Test]
    public function it_caches_permissions_for_user(): void
    {
        $customerRole = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $adminRole = Role::query()->where('slug', RoleSlug::Admin->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $customerRole->id]);

        $this->service->forUser($user);

        $user->update(['role_id' => $adminRole->id]);

        $cached = $this->service->forUser($user);

        $this->assertSame(RoleSlug::Customer, $cached->role);
    }

    #[Test]
    public function it_forgets_cached_permissions(): void
    {
        $customerRole = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $adminRole = Role::query()->where('slug', RoleSlug::Admin->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $customerRole->id]);

        $this->service->forUser($user);
        $this->service->forgetUser($user);

        $user->update(['role_id' => $adminRole->id]);
        $user->refresh()->load('role');

        $dto = $this->service->forUser($user);

        $this->assertSame(RoleSlug::Admin, $dto->role);
        $this->assertTrue($dto->allows(Permission::AdminUsersView));
    }

    #[Test]
    public function it_checks_role_membership(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Admin->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($this->service->userHasRole($user, RoleSlug::Admin));
        $this->assertFalse($this->service->userHasRole($user, RoleSlug::Customer));
    }
}

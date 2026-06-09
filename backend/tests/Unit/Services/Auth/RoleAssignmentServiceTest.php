<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Auth;

use App\DTOs\Auth\AssignRoleDTO;
use App\Enums\AuditAction;
use App\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use App\Repositories\Eloquent\EloquentRoleRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Services\Audit\AuditDispatcher;
use App\Services\Auth\RoleAssignmentService;
use App\Services\Infrastructure\CacheService;
use App\Support\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RoleAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private RoleAssignmentService $service;

    private CacheService $cache;

    protected function setUp(): void
    {
        parent::setUp();

        config(['audit.enabled' => true, 'audit.async' => false]);

        (new RoleSeeder)->run();

        $this->cache = app(CacheService::class);
        $this->service = new RoleAssignmentService(
            new EloquentUserRepository,
            new EloquentRoleRepository,
            $this->cache,
            app(AuditDispatcher::class),
        );
    }

    #[Test]
    public function it_assigns_role_and_returns_user_dto(): void
    {
        $customerRole = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $adminRole = Role::query()->where('slug', RoleSlug::Admin->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $customerRole->id]);

        $result = $this->service->assign(new AssignRoleDTO(
            userUuid: $user->uuid,
            roleSlug: RoleSlug::Admin,
            actorUuid: '660e8400-e29b-41d4-a716-446655440001',
        ));

        $this->assertSame($user->uuid, $result->uuid);
        $this->assertSame(RoleSlug::Admin, $result->role->slug);
        $this->assertSame('admin', $result->toArray()['role']['slug']);
        $this->assertDatabaseHas('users', [
            'uuid' => $user->uuid,
            'role_id' => $adminRole->id,
        ]);
    }

    #[Test]
    public function it_is_idempotent_when_user_already_has_target_role(): void
    {
        $adminRole = Role::query()->where('slug', RoleSlug::Admin->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $adminRole->id]);

        $result = $this->service->assign(new AssignRoleDTO(
            userUuid: $user->uuid,
            roleSlug: RoleSlug::Admin,
        ));

        $this->assertSame(RoleSlug::Admin, $result->role->slug);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    #[Test]
    public function it_clears_user_permissions_cache_on_role_change(): void
    {
        $customerRole = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $customerRole->id]);

        $this->cache->put('user_permissions', ['user_uuid' => $user->uuid], ['can_manage' => true]);

        $this->service->assign(new AssignRoleDTO(
            userUuid: $user->uuid,
            roleSlug: RoleSlug::Admin,
        ));

        $this->assertNull($this->cache->get('user_permissions', ['user_uuid' => $user->uuid]));
    }

    #[Test]
    public function it_records_audit_event_when_role_changes(): void
    {
        $customerRole = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $customerRole->id]);
        $actorUuid = '660e8400-e29b-41d4-a716-446655440001';

        $this->service->assign(new AssignRoleDTO(
            userUuid: $user->uuid,
            roleSlug: RoleSlug::Admin,
            actorUuid: $actorUuid,
        ));

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Updated->value,
            'subject_type' => 'user',
            'subject_uuid' => $user->uuid,
            'actor_uuid' => $actorUuid,
        ]);

        $auditLog = \App\Models\AuditLog::query()->first();
        $this->assertSame('customer', $auditLog->metadata['previous_role']);
        $this->assertSame('admin', $auditLog->metadata['new_role']);
    }

    #[Test]
    public function it_throws_when_user_uuid_is_not_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->service->assign(new AssignRoleDTO(
            userUuid: UuidGenerator::generate(),
            roleSlug: RoleSlug::Admin,
        ));
    }

    #[Test]
    public function it_throws_when_role_slug_is_not_found(): void
    {
        $customerRole = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $customerRole->id]);

        Role::query()->where('slug', RoleSlug::SuperAdmin->value)->delete();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->service->assign(new AssignRoleDTO(
            userUuid: $user->uuid,
            roleSlug: RoleSlug::SuperAdmin,
        ));
    }
}

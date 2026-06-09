<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Enums\RoleSlug;
use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use App\Support\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UserModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_receives_uuid_on_creation(): void
    {
        $user = User::factory()->create();

        $this->assertNotEmpty($user->uuid);
        $this->assertTrue(UuidGenerator::isValid($user->uuid));
    }

    #[Test]
    public function user_internal_id_and_role_id_are_not_exposed_in_array(): void
    {
        $user = User::factory()->create();

        $array = $user->toArray();

        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayNotHasKey('role_id', $array);
        $this->assertArrayHasKey('uuid', $array);
    }

    #[Test]
    public function user_route_key_is_uuid(): void
    {
        $this->assertSame('uuid', (new User)->getRouteKeyName());
    }

    #[Test]
    public function user_can_be_resolved_by_uuid_route_key(): void
    {
        $user = User::factory()->create();

        $resolved = (new User)->resolveRouteBinding($user->uuid);

        $this->assertTrue($user->is($resolved));
    }

    #[Test]
    public function user_belongs_to_role(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($role->is($user->role));
    }

    #[Test]
    public function user_status_is_cast_to_enum(): void
    {
        $user = User::factory()->suspended()->create();

        $this->assertSame(UserStatus::Suspended, $user->status);
        $this->assertSame('suspended', $user->toArray()['status']);
    }

    #[Test]
    public function user_factory_admin_state_assigns_admin_role(): void
    {
        $user = User::factory()->admin()->create();

        $this->assertSame(RoleSlug::Admin, $user->role->slug);
    }

    #[Test]
    public function users_table_has_domain_columns(): void
    {
        $user = User::factory()->withLastLogin()->create();

        $this->assertDatabaseHas('users', [
            'uuid' => $user->uuid,
            'email' => $user->email,
            'status' => UserStatus::Active->value,
        ]);
        $this->assertNotNull($user->last_login_at);
    }
}

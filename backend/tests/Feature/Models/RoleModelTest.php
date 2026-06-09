<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use App\Support\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RoleModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function role_receives_uuid_on_creation(): void
    {
        $role = Role::factory()->customer()->create();

        $this->assertNotEmpty($role->uuid);
        $this->assertTrue(UuidGenerator::isValid($role->uuid));
    }

    #[Test]
    public function role_internal_id_is_not_exposed_in_array(): void
    {
        $role = Role::factory()->customer()->create();

        $this->assertArrayNotHasKey('id', $role->toArray());
        $this->assertArrayHasKey('uuid', $role->toArray());
    }

    #[Test]
    public function role_route_key_is_uuid(): void
    {
        $this->assertSame('uuid', (new Role)->getRouteKeyName());
    }

    #[Test]
    public function role_slug_is_cast_to_enum(): void
    {
        $role = Role::factory()->admin()->create();

        $this->assertSame(RoleSlug::Admin, $role->slug);
        $this->assertSame('admin', $role->toArray()['slug']);
    }

    #[Test]
    public function role_has_many_users(): void
    {
        $role = Role::factory()->customer()->create();
        User::factory()->count(2)->create(['role_id' => $role->id]);

        $this->assertCount(2, $role->users);
    }
}

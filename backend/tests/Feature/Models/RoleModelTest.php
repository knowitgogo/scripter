<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

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
    public function role_has_many_users(): void
    {
        $role = Role::factory()->customer()->create();
        User::factory()->count(2)->create(['role_id' => $role->id]);

        $this->assertCount(2, $role->users);
    }
}

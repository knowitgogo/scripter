<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\User;
use App\Support\UuidGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UserUuidTest extends TestCase
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
    public function user_internal_id_is_not_exposed_in_array(): void
    {
        $user = User::factory()->create();

        $this->assertArrayNotHasKey('id', $user->toArray());
        $this->assertArrayHasKey('uuid', $user->toArray());
    }

    #[Test]
    public function user_route_key_is_uuid(): void
    {
        $user = new User;

        $this->assertSame('uuid', $user->getRouteKeyName());
    }

    #[Test]
    public function user_can_be_resolved_by_uuid_route_key(): void
    {
        $user = User::factory()->create();

        $resolved = (new User)->resolveRouteBinding($user->uuid);

        $this->assertTrue($user->is($resolved));
    }
}

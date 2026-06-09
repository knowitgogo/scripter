<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Auth;

use App\DTOs\Auth\UserDTO;
use App\Enums\RoleSlug;
use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UserDTOTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_maps_user_model_to_dto_without_internal_ids(): void
    {
        $role = Role::factory()->customer()->create();
        $user = User::factory()->create([
            'role_id' => $role->id,
            'status' => UserStatus::Active,
        ]);

        $dto = UserDTO::fromModel($user);
        $array = $dto->toArray();

        $this->assertSame($user->uuid, $dto->uuid);
        $this->assertSame(RoleSlug::Customer, $dto->role->slug);
        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayNotHasKey('role_id', $array);
        $this->assertSame('customer', $array['role']['slug']);
        $this->assertSame('active', $array['status']);
    }
}

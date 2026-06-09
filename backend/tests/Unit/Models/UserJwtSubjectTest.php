<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UserJwtSubjectTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_implements_jwt_subject(): void
    {
        $this->assertInstanceOf(JWTSubject::class, new User);
    }

    #[Test]
    public function jwt_identifier_uses_public_uuid(): void
    {
        $user = User::factory()->create();

        $this->assertSame($user->uuid, $user->getJWTIdentifier());
        $this->assertSame('uuid', $user->getAuthIdentifierName());
        $this->assertSame($user->uuid, $user->getAuthIdentifier());
    }

    #[Test]
    public function jwt_custom_claims_include_role(): void
    {
        $role = Role::factory()->superAdmin()->create();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertSame(
            ['role' => RoleSlug::SuperAdmin->value],
            $user->getJWTCustomClaims(),
        );
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use App\Support\Auth\JwtClaimBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class JwtClaimBuilderTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_includes_role_slug_claim(): void
    {
        $role = Role::factory()->admin()->create();
        $user = User::factory()->create(['role_id' => $role->id]);

        $claims = JwtClaimBuilder::forUser($user);

        $this->assertSame(['role' => RoleSlug::Admin->value], $claims);
    }
}

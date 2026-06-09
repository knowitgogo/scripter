<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class JwtAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();
    }

    #[Test]
    public function it_issues_token_with_uuid_subject_and_role_claim(): void
    {
        $adminRole = Role::query()->where('slug', RoleSlug::Admin->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $adminRole->id]);

        $token = auth('api')->login($user);
        $payload = auth('api')->setToken($token)->payload();

        $this->assertNotEmpty($token);
        $this->assertSame($user->uuid, $payload->get('sub'));
        $this->assertSame('admin', $payload->get('role'));
    }

    #[Test]
    public function it_authenticates_protected_route_with_bearer_token(): void
    {
        Route::middleware('auth:api')->get('api/v1/test-jwt-protected', function (): array {
            return ['uuid' => auth('api')->user()?->uuid];
        });

        $customerRole = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $customerRole->id]);
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/test-jwt-protected');

        $response->assertOk();
        $response->assertJson(['uuid' => $user->uuid]);
    }

    #[Test]
    public function protected_route_returns_401_without_token(): void
    {
        Route::middleware('auth:api')->get('api/v1/test-jwt-unauthorized', fn (): string => 'ok');

        $response = $this->getJson('/api/v1/test-jwt-unauthorized');

        $response->assertUnauthorized();
        $response->assertJson([
            'success' => false,
            'errors' => [],
        ]);
    }

    #[Test]
    public function it_refreshes_token_within_refresh_window(): void
    {
        $customerRole = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $customerRole->id]);
        $token = auth('api')->login($user);

        $refreshed = auth('api')->setToken($token)->refresh();

        $this->assertNotSame($token, $refreshed);
        $this->assertSame($user->uuid, auth('api')->setToken($refreshed)->payload()->get('sub'));
    }
}

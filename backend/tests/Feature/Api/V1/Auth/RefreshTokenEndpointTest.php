<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Enums\AuditAction;
use App\Enums\RoleSlug;
use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RefreshTokenEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['audit.enabled' => true, 'audit.async' => false]);

        (new RoleSeeder)->run();
    }

    #[Test]
    public function refresh_endpoint_returns_new_jwt_envelope(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $role->id]);
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/refresh');

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => ['access_token', 'token_type', 'expires_in'],
            'message',
            'errors',
        ]);
        $response->assertJson([
            'success' => true,
            'data' => [
                'token_type' => 'bearer',
                'expires_in' => 3600,
            ],
            'errors' => [],
        ]);

        $newToken = (string) $response->json('data.access_token');
        $this->assertNotSame($token, $newToken);
        $this->assertSame($user->uuid, auth('api')->setToken($newToken)->payload()->get('sub'));
    }

    #[Test]
    public function refresh_endpoint_new_token_authenticates_protected_route(): void
    {
        Route::middleware('auth:api')->get('api/v1/test-after-refresh', fn (): string => 'ok');

        $role = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $role->id]);
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/refresh');

        $response->assertOk();

        $newToken = (string) $response->json('data.access_token');

        $this->withHeader('Authorization', 'Bearer '.$newToken)
            ->getJson('/api/v1/test-after-refresh')
            ->assertOk();
    }

    #[Test]
    public function refresh_endpoint_returns_401_without_token(): void
    {
        $response = $this->postJson('/api/v1/auth/refresh');

        $response->assertUnauthorized();
        $response->assertJson([
            'success' => false,
            'errors' => [],
        ]);
    }

    #[Test]
    public function refresh_endpoint_returns_403_for_suspended_account(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $user = User::factory()->create([
            'role_id' => $role->id,
            'status' => UserStatus::Suspended,
        ]);
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/refresh');

        $response->assertForbidden();
        $response->assertJson([
            'success' => false,
            'message' => 'Account is not active.',
            'errors' => [],
        ]);
    }

    #[Test]
    public function refresh_endpoint_records_audit_event(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $role->id]);
        $token = auth('api')->login($user);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/refresh')
            ->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Authenticated->value,
            'subject_uuid' => $user->uuid,
        ]);
    }
}

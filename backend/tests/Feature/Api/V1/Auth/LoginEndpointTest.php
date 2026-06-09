<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Enums\AuditAction;
use App\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LoginEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['audit.enabled' => true, 'audit.async' => false]);

        (new RoleSeeder)->run();
    }

    #[Test]
    public function login_endpoint_returns_jwt_envelope(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $user = User::factory()->create([
            'role_id' => $role->id,
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

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

        $token = (string) $response->json('data.access_token');
        $this->assertNotEmpty($token);
        $this->assertSame($user->uuid, auth('api')->setToken($token)->payload()->get('sub'));
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    #[Test]
    public function login_endpoint_returns_401_for_invalid_credentials(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        User::factory()->create([
            'role_id' => $role->id,
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized();
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid credentials.',
            'errors' => [],
        ]);
    }

    #[Test]
    public function login_endpoint_returns_403_for_suspended_account(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        User::factory()->suspended()->create([
            'role_id' => $role->id,
            'email' => 'suspended@example.com',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'suspended@example.com',
            'password' => 'password',
        ]);

        $response->assertForbidden();
        $response->assertJson([
            'success' => false,
            'message' => 'Account is not active.',
            'errors' => [],
        ]);
    }

    #[Test]
    public function login_endpoint_returns_422_for_validation_errors(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertUnprocessable();
        $response->assertJson([
            'success' => false,
            'message' => 'Validation failed.',
        ]);
        $response->assertJsonStructure(['errors']);
    }

    #[Test]
    public function login_endpoint_records_audit_event(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $user = User::factory()->create([
            'role_id' => $role->id,
            'email' => 'audit@example.com',
            'password' => 'password',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'audit@example.com',
            'password' => 'password',
        ])->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Authenticated->value,
            'subject_uuid' => $user->uuid,
        ]);
    }
}

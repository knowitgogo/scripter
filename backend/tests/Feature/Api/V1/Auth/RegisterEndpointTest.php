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

final class RegisterEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['audit.enabled' => true, 'audit.async' => false]);

        (new RoleSeeder)->run();
    }

    #[Test]
    public function register_endpoint_creates_user_and_returns_jwt_envelope(): void
    {
        $customerRole = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated();
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

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'name' => 'Jane Doe',
            'role_id' => $customerRole->id,
            'status' => 'active',
        ]);

        $user = User::query()->where('email', 'jane@example.com')->firstOrFail();
        $token = (string) $response->json('data.access_token');
        $this->assertSame($user->uuid, auth('api')->setToken($token)->payload()->get('sub'));
        $this->assertSame('customer', auth('api')->setToken($token)->payload()->get('role'));
    }

    #[Test]
    public function register_endpoint_returns_422_for_duplicate_email(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        User::factory()->create([
            'role_id' => $role->id,
            'email' => 'taken@example.com',
        ]);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Another User',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertUnprocessable();
        $response->assertJson([
            'success' => false,
            'message' => 'Validation failed.',
        ]);
    }

    #[Test]
    public function register_endpoint_returns_422_when_password_confirmation_mismatches(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ]);

        $response->assertUnprocessable();
        $response->assertJson([
            'success' => false,
            'message' => 'Validation failed.',
        ]);
    }

    #[Test]
    public function register_endpoint_returns_422_for_missing_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/register', []);

        $response->assertUnprocessable();
        $response->assertJson([
            'success' => false,
            'message' => 'Validation failed.',
        ]);
        $response->assertJsonStructure(['errors']);
    }

    #[Test]
    public function register_endpoint_records_audit_events(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Audit User',
            'email' => 'audit@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        $user = User::query()->where('email', 'audit@example.com')->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Created->value,
            'subject_uuid' => $user->uuid,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Authenticated->value,
            'subject_uuid' => $user->uuid,
        ]);
    }
}

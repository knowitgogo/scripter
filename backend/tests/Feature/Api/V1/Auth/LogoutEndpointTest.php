<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Enums\AuditAction;
use App\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LogoutEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['audit.enabled' => true, 'audit.async' => false]);

        (new RoleSeeder)->run();
    }

    #[Test]
    public function logout_endpoint_invalidates_token_and_returns_envelope(): void
    {
        Route::middleware('auth:api')->get('api/v1/test-after-logout', fn (): string => 'ok');

        $role = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $role->id]);
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout');

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'message' => 'Successfully logged out.',
            ],
            'errors' => [],
        ]);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/test-after-logout')
            ->assertUnauthorized();
    }

    #[Test]
    public function logout_endpoint_returns_401_without_token(): void
    {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertUnauthorized();
        $response->assertJson([
            'success' => false,
            'errors' => [],
        ]);
    }

    #[Test]
    public function logout_endpoint_records_audit_event(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $role->id]);
        $token = auth('api')->login($user);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::LoggedOut->value,
            'subject_uuid' => $user->uuid,
        ]);
    }
}

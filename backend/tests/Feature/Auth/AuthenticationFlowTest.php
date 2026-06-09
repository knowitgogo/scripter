<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\AuditAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\TestCase;

/**
 * End-to-end authentication lifecycle integration tests.
 */
final class AuthenticationFlowTest extends TestCase
{
    use InteractsWithAuthentication, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function registration_flow_issues_token_and_exposes_profile_via_me(): void
    {
        $registerResponse = $this->postJson('/api/v1/auth/register', $this->registerPayload([
            'name' => 'Flow User',
            'email' => 'flow-register@example.com',
        ]));

        $this->assertSuccessfulApiEnvelope($registerResponse, 201);

        $token = $this->extractAccessToken($registerResponse);

        $meResponse = $this->withBearerToken($token)->getJson('/api/v1/me');

        $this->assertSuccessfulApiEnvelope($meResponse);
        $meResponse->assertJsonPath('data.email', 'flow-register@example.com');
        $meResponse->assertJsonPath('data.name', 'Flow User');
        $meResponse->assertJsonPath('data.role.slug', 'customer');
    }

    #[Test]
    public function login_flow_issues_token_and_updates_last_login_at(): void
    {
        $user = $this->createAuthUser([
            'email' => 'flow-login@example.com',
            'password' => 'password',
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', $this->loginPayload($user));

        $this->assertSuccessfulApiEnvelope($loginResponse);

        $token = $this->extractAccessToken($loginResponse);
        $this->assertSame($user->uuid, auth('api')->setToken($token)->payload()->get('sub'));
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    #[Test]
    public function full_session_lifecycle_register_me_refresh_logout(): void
    {
        $registerResponse = $this->postJson('/api/v1/auth/register', $this->registerPayload([
            'email' => 'lifecycle@example.com',
        ]));

        $registerResponse->assertCreated();
        $originalToken = $this->extractAccessToken($registerResponse);

        $this->withBearerToken($originalToken)
            ->getJson('/api/v1/me')
            ->assertOk();

        $refreshResponse = $this->withBearerToken($originalToken)
            ->postJson('/api/v1/auth/refresh');

        $this->assertSuccessfulApiEnvelope($refreshResponse);

        $refreshedToken = $this->extractAccessToken($refreshResponse);
        $this->assertNotSame($originalToken, $refreshedToken);

        $this->withBearerToken($refreshedToken)
            ->getJson('/api/v1/me')
            ->assertOk();

        $this->withBearerToken($refreshedToken)
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJsonPath('data.message', 'Successfully logged out.');

        $this->withBearerToken($refreshedToken)
            ->getJson('/api/v1/me')
            ->assertUnauthorized();
    }

    #[Test]
    public function login_flow_records_authentication_audit_event(): void
    {
        $user = $this->createAuthUser([
            'email' => 'flow-audit@example.com',
            'password' => 'password',
        ]);

        $this->postJson('/api/v1/auth/login', $this->loginPayload($user))->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Authenticated->value,
            'subject_uuid' => $user->uuid,
        ]);
    }

    #[Test]
    public function registration_persists_user_with_customer_role(): void
    {
        $this->postJson('/api/v1/auth/register', $this->registerPayload([
            'email' => 'persisted@example.com',
        ]))->assertCreated();

        $user = User::query()->where('email', 'persisted@example.com')->firstOrFail();

        $this->assertSame('customer', $user->role->slug->value);
        $this->assertSame('active', $user->status->value);
    }
}

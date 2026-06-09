<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Enums\RoleSlug;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\TestCase;

final class AuthenticationHelpersTest extends TestCase
{
    use InteractsWithAuthentication, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    #[Test]
    public function helpers_seed_roles_and_issue_jwt_for_user(): void
    {
        $user = $this->createAuthUser(['email' => 'helpers@example.com']);

        $token = $this->jwtTokenFor($user);

        $this->assertNotEmpty($token);
        $this->assertSame($user->uuid, auth('api')->setToken($token)->payload()->get('sub'));
        $this->assertSame(RoleSlug::Customer, $user->role->slug);
    }

    #[Test]
    public function register_payload_includes_password_confirmation(): void
    {
        $payload = $this->registerPayload(['email' => 'payload@example.com']);

        $this->assertSame('payload@example.com', $payload['email']);
        $this->assertSame($payload['password'], $payload['password_confirmation']);
    }

    #[Test]
    public function login_payload_uses_user_email(): void
    {
        $user = $this->createAuthUser(['email' => 'login-payload@example.com']);

        $payload = $this->loginPayload($user);

        $this->assertSame('login-payload@example.com', $payload['email']);
        $this->assertSame('password', $payload['password']);
    }
}

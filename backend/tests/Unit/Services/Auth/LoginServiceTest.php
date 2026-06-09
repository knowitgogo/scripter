<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Auth;

use App\DTOs\Auth\LoginDTO;
use App\Enums\AuditAction;
use App\Enums\RoleSlug;
use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Services\Audit\AuditDispatcher;
use App\Services\Auth\LoginService;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LoginServiceTest extends TestCase
{
    use RefreshDatabase;

    private LoginService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config(['audit.enabled' => true, 'audit.async' => false]);

        (new RoleSeeder)->run();

        $this->service = new LoginService(
            new EloquentUserRepository,
            app(AuditDispatcher::class),
        );
    }

    #[Test]
    public function it_returns_auth_token_for_valid_credentials(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $user = User::factory()->create([
            'role_id' => $role->id,
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $token = $this->service->login(new LoginDTO(
            email: 'login@example.com',
            password: 'password',
        ));

        $this->assertSame('bearer', $token->token_type);
        $this->assertSame(3600, $token->expires_in);
        $this->assertNotEmpty($token->access_token);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    #[Test]
    public function it_records_authentication_audit_event(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $user = User::factory()->create([
            'role_id' => $role->id,
            'email' => 'audit@example.com',
            'password' => 'password',
        ]);

        $this->service->login(new LoginDTO(
            email: 'audit@example.com',
            password: 'password',
        ));

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Authenticated->value,
            'subject_type' => 'user',
            'subject_uuid' => $user->uuid,
        ]);
    }

    #[Test]
    public function it_throws_when_credentials_are_invalid(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        User::factory()->create([
            'role_id' => $role->id,
            'email' => 'wrong@example.com',
            'password' => 'password',
        ]);

        $this->expectException(AuthenticationException::class);

        $this->service->login(new LoginDTO(
            email: 'wrong@example.com',
            password: 'not-the-password',
        ));
    }

    #[Test]
    public function it_throws_when_account_is_not_active(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        User::factory()->suspended()->create([
            'role_id' => $role->id,
            'email' => 'suspended@example.com',
            'password' => 'password',
        ]);

        $this->expectException(\App\Exceptions\DomainException::class);

        $this->service->login(new LoginDTO(
            email: 'suspended@example.com',
            password: 'password',
        ));
    }
}

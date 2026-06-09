<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Auth;

use App\DTOs\Auth\RegisterDTO;
use App\Enums\AuditAction;
use App\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Eloquent\EloquentRoleRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Services\Audit\AuditDispatcher;
use App\Services\Auth\RegisterService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RegisterServiceTest extends TestCase
{
    use RefreshDatabase;

    private RegisterService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config(['audit.enabled' => true, 'audit.async' => false]);

        (new RoleSeeder)->run();

        $this->service = new RegisterService(
            new EloquentUserRepository,
            new EloquentRoleRepository,
            app(AuditDispatcher::class),
        );
    }

    #[Test]
    public function it_creates_customer_user_and_returns_auth_token(): void
    {
        $customerRole = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();

        $token = $this->service->register(new RegisterDTO(
            name: 'New User',
            email: 'new@example.com',
            password: 'password123',
        ));

        $this->assertSame('bearer', $token->token_type);
        $this->assertNotEmpty($token->access_token);

        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'name' => 'New User',
            'role_id' => $customerRole->id,
            'status' => 'active',
        ]);

        $user = User::query()->where('email', 'new@example.com')->firstOrFail();
        $this->assertNotEmpty($user->uuid);
        $this->assertNotNull($user->last_login_at);
        $this->assertSame($user->uuid, auth('api')->setToken($token->access_token)->payload()->get('sub'));
    }

    #[Test]
    public function it_records_created_and_authenticated_audit_events(): void
    {
        $this->service->register(new RegisterDTO(
            name: 'Audit User',
            email: 'audit-register@example.com',
            password: 'password123',
        ));

        $user = User::query()->where('email', 'audit-register@example.com')->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Created->value,
            'subject_type' => 'user',
            'subject_uuid' => $user->uuid,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Authenticated->value,
            'subject_uuid' => $user->uuid,
        ]);
    }
}

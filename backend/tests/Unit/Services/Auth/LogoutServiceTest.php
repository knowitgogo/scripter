<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Auth;

use App\Enums\AuditAction;
use App\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use App\Services\Audit\AuditDispatcher;
use App\Services\Auth\LogoutService;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LogoutServiceTest extends TestCase
{
    use RefreshDatabase;

    private LogoutService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config(['audit.enabled' => true, 'audit.async' => false]);

        (new RoleSeeder)->run();

        $this->service = new LogoutService(app(AuditDispatcher::class));
    }

    #[Test]
    public function it_invalidates_token_and_returns_result_dto(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $role->id]);
        $token = auth('api')->login($user);

        auth('api')->setToken($token);

        $result = $this->service->logout();

        $this->assertSame('Successfully logged out.', $result->message);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::LoggedOut->value,
            'subject_type' => 'user',
            'subject_uuid' => $user->uuid,
        ]);
    }

    #[Test]
    public function it_throws_when_user_is_not_authenticated(): void
    {
        $this->expectException(AuthenticationException::class);

        $this->service->logout();
    }
}

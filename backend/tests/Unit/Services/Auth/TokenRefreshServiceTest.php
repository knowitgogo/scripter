<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Auth;

use App\Enums\AuditAction;
use App\Enums\RoleSlug;
use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Services\Audit\AuditDispatcher;
use App\Services\Auth\TokenRefreshService;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TokenRefreshServiceTest extends TestCase
{
    use RefreshDatabase;

    private TokenRefreshService $service;

    private EloquentUserRepository $users;

    protected function setUp(): void
    {
        parent::setUp();

        config(['audit.enabled' => true, 'audit.async' => false]);

        (new RoleSeeder)->run();

        $this->users = new EloquentUserRepository;
        $this->service = new TokenRefreshService(
            $this->users,
            app(AuditDispatcher::class),
        );
    }

    #[Test]
    public function it_returns_new_auth_token_within_refresh_window(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $role->id]);
        $token = auth('api')->login($user);

        request()->headers->set('Authorization', 'Bearer '.$token);

        $result = $this->service->refresh();

        $this->assertSame('bearer', $result->token_type);
        $this->assertSame(3600, $result->expires_in);
        $this->assertNotSame($token, $result->access_token);
        $this->assertSame($user->uuid, auth('api')->setToken($result->access_token)->payload()->get('sub'));
    }

    #[Test]
    public function it_records_refresh_audit_event(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $role->id]);
        $token = auth('api')->login($user);

        request()->headers->set('Authorization', 'Bearer '.$token);
        $this->service->refresh();

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::Authenticated->value,
            'subject_uuid' => $user->uuid,
        ]);

        $auditLog = \App\Models\AuditLog::query()->first();
        $this->assertSame('jwt_refresh', $auditLog->metadata['method']);
    }

    #[Test]
    public function it_throws_when_token_is_missing(): void
    {
        $this->expectException(AuthenticationException::class);

        $this->service->refresh();
    }

    #[Test]
    public function it_throws_when_account_is_not_active(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $user = User::factory()->create([
            'role_id' => $role->id,
            'status' => UserStatus::Active,
        ]);
        $token = auth('api')->login($user);

        $this->users->update($user, ['status' => UserStatus::Suspended]);

        request()->headers->set('Authorization', 'Bearer '.$token);

        $this->expectException(\App\Exceptions\DomainException::class);

        $this->service->refresh();
    }
}

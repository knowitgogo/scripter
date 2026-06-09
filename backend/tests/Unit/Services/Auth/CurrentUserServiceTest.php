<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Auth;

use App\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Services\Auth\CurrentUserService;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CurrentUserServiceTest extends TestCase
{
    use RefreshDatabase;

    private CurrentUserService $service;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();

        $this->service = new CurrentUserService(new EloquentUserRepository);
    }

    #[Test]
    public function it_returns_authenticated_user_dto(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Admin->value)->firstOrFail();
        $user = User::factory()->create([
            'role_id' => $role->id,
            'email' => 'me@example.com',
        ]);

        auth('api')->login($user);

        $dto = $this->service->get();

        $this->assertSame($user->uuid, $dto->uuid);
        $this->assertSame('me@example.com', $dto->email);
        $this->assertSame(RoleSlug::Admin, $dto->role->slug);
        $this->assertArrayNotHasKey('id', $dto->toArray());
    }

    #[Test]
    public function it_throws_when_user_is_not_authenticated(): void
    {
        $this->expectException(AuthenticationException::class);

        $this->service->get();
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Middleware;

use App\Enums\Permission;
use App\Enums\RoleSlug;
use App\Http\Middleware\EnsurePermission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EnsurePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();
    }

    #[Test]
    public function it_allows_request_when_user_has_permission(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Admin->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->mockAuthUser($user);

        $middleware = $this->app->make(EnsurePermission::class);
        $request = Request::create('/api/v1/admin/users', 'GET');
        $called = false;

        $response = $middleware->handle($request, function () use (&$called) {
            $called = true;

            return response()->json(['ok' => true]);
        }, Permission::AdminUsersView->value);

        $this->assertTrue($called);
        $this->assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function it_throws_authentication_exception_when_user_is_missing(): void
    {
        $this->mockAuthUser(null);

        $middleware = $this->app->make(EnsurePermission::class);
        $request = Request::create('/api/v1/websites', 'GET');

        $this->expectException(AuthenticationException::class);

        $middleware->handle($request, fn () => response()->json(['ok' => true]), Permission::WebsitesView->value);
    }

    #[Test]
    public function it_throws_authorization_exception_when_user_lacks_permission(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->mockAuthUser($user);

        $middleware = $this->app->make(EnsurePermission::class);
        $request = Request::create('/api/v1/admin/users', 'GET');

        $this->expectException(AuthorizationException::class);

        $middleware->handle($request, fn () => response()->json(['ok' => true]), Permission::AdminUsersView->value);
    }

    private function mockAuthUser(?User $user): void
    {
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->once()->andReturn($user);

        $factory = Mockery::mock(AuthFactory::class);
        $factory->shouldReceive('guard')->with('api')->andReturn($guard);

        $this->instance(AuthFactory::class, $factory);
    }
}

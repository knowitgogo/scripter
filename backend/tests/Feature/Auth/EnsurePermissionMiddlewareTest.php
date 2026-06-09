<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\TestCase;

final class EnsurePermissionMiddlewareTest extends TestCase
{
    use InteractsWithAuthentication, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();

        Route::middleware(['auth:api', 'permission:'.Permission::AdminUsersView->value])
            ->get('api/v1/test-permission-protected', fn (): array => ['ok' => true]);
    }

    #[Test]
    public function middleware_allows_user_with_required_permission(): void
    {
        $user = $this->createAuthUser([
            'role_id' => $this->adminRole()->id,
        ]);

        $this->actingAsJwt($user)
            ->getJson('/api/v1/test-permission-protected')
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    #[Test]
    public function middleware_returns_403_without_required_permission(): void
    {
        $user = $this->createAuthUser();

        $this->actingAsJwt($user)
            ->getJson('/api/v1/test-permission-protected')
            ->assertForbidden()
            ->assertJson([
                'success' => false,
                'message' => 'Forbidden.',
                'errors' => [],
            ]);
    }

    #[Test]
    public function middleware_returns_401_without_authentication(): void
    {
        $this->getJson('/api/v1/test-permission-protected')
            ->assertUnauthorized();
    }
}

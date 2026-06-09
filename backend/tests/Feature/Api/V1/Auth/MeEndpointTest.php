<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class MeEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleSeeder)->run();
    }

    #[Test]
    public function me_endpoint_returns_authenticated_user_envelope(): void
    {
        $role = Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
        $user = User::factory()->create([
            'role_id' => $role->id,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);
        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/me');

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'uuid',
                'name',
                'email',
                'status',
                'role' => ['uuid', 'name', 'slug'],
                'last_login_at',
                'created_at',
                'updated_at',
            ],
            'message',
            'errors',
        ]);
        $response->assertJson([
            'success' => true,
            'data' => [
                'uuid' => $user->uuid,
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'status' => 'active',
                'role' => [
                    'slug' => 'customer',
                ],
            ],
            'errors' => [],
        ]);
        $this->assertArrayNotHasKey('id', $response->json('data'));
        $this->assertArrayNotHasKey('role_id', $response->json('data'));
    }

    #[Test]
    public function me_endpoint_returns_401_without_token(): void
    {
        $response = $this->getJson('/api/v1/me');

        $response->assertUnauthorized();
        $response->assertJson([
            'success' => false,
            'errors' => [],
        ]);
    }
}

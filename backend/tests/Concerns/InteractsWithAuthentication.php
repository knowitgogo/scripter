<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Testing\TestResponse;

/**
 * JWT authentication helpers for feature and integration tests.
 */
trait InteractsWithAuthentication
{
    protected function setUpAuthentication(): void
    {
        config(['audit.enabled' => true, 'audit.async' => false]);

        $this->seedAuthRoles();
    }

    protected function seedAuthRoles(): void
    {
        (new RoleSeeder)->run();
    }

    protected function customerRole(): Role
    {
        return Role::query()->where('slug', RoleSlug::Customer->value)->firstOrFail();
    }

    protected function adminRole(): Role
    {
        return Role::query()->where('slug', RoleSlug::Admin->value)->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function createAuthUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role_id' => $this->customerRole()->id,
        ], $attributes));
    }

    protected function jwtTokenFor(User $user): string
    {
        return auth('api')->login($user);
    }

    protected function withBearerToken(string $token): static
    {
        return $this->withHeader('Authorization', 'Bearer '.$token);
    }

    protected function actingAsJwt(User $user): static
    {
        return $this->withBearerToken($this->jwtTokenFor($user));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, string>
     */
    protected function registerPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, string>
     */
    protected function loginPayload(User $user, array $overrides = []): array
    {
        return array_merge([
            'email' => $user->email,
            'password' => 'password',
        ], $overrides);
    }

    protected function assertSuccessfulApiEnvelope(TestResponse $response, int $status = 200): void
    {
        $response->assertStatus($status);
        $response->assertJsonStructure(['success', 'data', 'message', 'errors']);
        $response->assertJson([
            'success' => true,
            'errors' => [],
        ]);
    }

    protected function assertErrorApiEnvelope(TestResponse $response, int $status): void
    {
        $response->assertStatus($status);
        $response->assertJsonStructure(['success', 'data', 'message', 'errors']);
        $response->assertJson([
            'success' => false,
            'errors' => [],
        ]);
    }

    protected function extractAccessToken(TestResponse $response): string
    {
        return (string) $response->json('data.access_token');
    }
}

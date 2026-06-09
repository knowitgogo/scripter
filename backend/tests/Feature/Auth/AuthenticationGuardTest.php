<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\InteractsWithAuthentication;
use Tests\TestCase;

/**
 * Cross-cutting authentication guard and validation tests.
 */
final class AuthenticationGuardTest extends TestCase
{
    use InteractsWithAuthentication, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthentication();
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function protectedAuthEndpoints(): array
    {
        return [
            'me' => ['GET', '/api/v1/me'],
            'logout' => ['POST', '/api/v1/auth/logout'],
            'refresh' => ['POST', '/api/v1/auth/refresh'],
        ];
    }

    #[Test]
    #[DataProvider('protectedAuthEndpoints')]
    public function protected_endpoints_require_bearer_token(string $method, string $uri): void
    {
        $response = $this->json($method, $uri);

        $this->assertErrorApiEnvelope($response, 401);
    }

    #[Test]
    #[DataProvider('protectedAuthEndpoints')]
    public function protected_endpoints_reject_invalid_bearer_token(string $method, string $uri): void
    {
        $response = $this->withBearerToken('not-a-valid-jwt')
            ->json($method, $uri);

        $this->assertErrorApiEnvelope($response, 401);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function publicAuthEndpoints(): array
    {
        return [
            'register' => ['POST', '/api/v1/auth/register'],
            'login' => ['POST', '/api/v1/auth/login'],
        ];
    }

    #[Test]
    #[DataProvider('publicAuthEndpoints')]
    public function public_endpoints_return_validation_envelope_for_empty_body(string $method, string $uri): void
    {
        $response = $this->json($method, $uri, []);

        $response->assertUnprocessable();
        $response->assertJson([
            'success' => false,
            'message' => 'Validation failed.',
        ]);
        $response->assertJsonStructure(['errors']);
    }
}

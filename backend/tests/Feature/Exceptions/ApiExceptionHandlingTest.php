<?php

declare(strict_types=1);

namespace Tests\Feature\Exceptions;

use App\Exceptions\DomainException;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ApiExceptionHandlingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::prefix('api/v1')->group(function (): void {
            Route::get('test-domain-exception', fn () => throw new DomainException('Resource conflict.', 409));
            Route::get('test-authentication', fn () => throw new AuthenticationException('Unauthenticated.'));
            Route::get('test-authorization', fn () => throw new AuthorizationException('Forbidden.'));
            Route::get('test-model-not-found', fn () => throw (new ModelNotFoundException)->setModel(User::class));
            Route::get('test-validation', function (): void {
                throw ValidationException::withMessages([
                    'email' => ['The email field is required.'],
                ]);
            });
            Route::get('test-unhandled', fn () => throw new \RuntimeException('Something broke.'));
        });
    }

    #[Test]
    public function domain_exception_renders_api_envelope(): void
    {
        $response = $this->getJson('/api/v1/test-domain-exception');

        $response->assertStatus(409);
        $response->assertJson([
            'success' => false,
            'message' => 'Resource conflict.',
            'errors' => [],
        ]);
    }

    #[Test]
    public function authentication_exception_renders_401_envelope(): void
    {
        $response = $this->getJson('/api/v1/test-authentication');

        $response->assertUnauthorized();
        $response->assertJson([
            'success' => false,
            'message' => 'Unauthenticated.',
            'errors' => [],
        ]);
    }

    #[Test]
    public function authorization_exception_renders_403_envelope(): void
    {
        $response = $this->getJson('/api/v1/test-authorization');

        $response->assertForbidden();
        $response->assertJson([
            'success' => false,
            'message' => 'Forbidden.',
            'errors' => [],
        ]);
    }

    #[Test]
    public function model_not_found_renders_404_envelope(): void
    {
        $response = $this->getJson('/api/v1/test-model-not-found');

        $response->assertNotFound();
        $response->assertJson([
            'success' => false,
            'message' => 'Resource not found.',
            'errors' => [],
        ]);
    }

    #[Test]
    public function validation_exception_renders_422_envelope_with_errors(): void
    {
        $response = $this->getJson('/api/v1/test-validation');

        $response->assertUnprocessable();
        $response->assertJson([
            'success' => false,
            'errors' => ['The email field is required.'],
        ]);
    }

    #[Test]
    public function unknown_api_route_renders_404_envelope(): void
    {
        $response = $this->getJson('/api/v1/does-not-exist');

        $response->assertNotFound();
        $response->assertJson([
            'success' => false,
            'message' => 'Resource not found.',
            'errors' => [],
        ]);
    }

    #[Test]
    public function unhandled_exception_renders_500_envelope_with_trace_id(): void
    {
        Config::set('app.debug', false);

        $response = $this->getJson('/api/v1/test-unhandled');

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'An unexpected error occurred.',
            'errors' => [],
        ]);
        $response->assertHeader('X-Trace-Id');
    }

    #[Test]
    public function api_routes_return_json_even_without_json_accept_header(): void
    {
        $response = $this->get('/api/v1/health', [
            'Accept' => 'text/html',
        ]);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Exceptions\DomainException;
use App\Support\ApiExceptionRenderer;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ApiExceptionRendererTest extends TestCase
{
    #[Test]
    public function should_handle_api_routes(): void
    {
        $request = Request::create('/api/v1/health', 'GET');

        $this->assertTrue(ApiExceptionRenderer::shouldHandle($request));
    }

    #[Test]
    public function should_not_handle_non_api_html_requests(): void
    {
        $request = Request::create('/legacy-page', 'GET', server: [
            'HTTP_ACCEPT' => 'text/html',
        ]);

        $this->assertFalse(ApiExceptionRenderer::shouldHandle($request));
    }

    #[Test]
    public function render_returns_null_for_non_api_requests(): void
    {
        $request = Request::create('/legacy-page', 'GET', server: [
            'HTTP_ACCEPT' => 'text/html',
        ]);

        $this->assertNull(
            ApiExceptionRenderer::render(new DomainException('Conflict.', 409), $request),
        );
    }

    #[Test]
    public function render_maps_validation_exception_to_envelope(): void
    {
        $request = Request::create('/api/v1/test', 'POST');
        $exception = ValidationException::withMessages([
            'name' => ['The name field is required.'],
        ]);

        $response = ApiExceptionRenderer::render($exception, $request);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'data' => [],
            'message' => $exception->getMessage(),
            'errors' => ['The name field is required.'],
        ], $response->getData(true));
    }

    #[Test]
    public function render_maps_authentication_exception_to_401(): void
    {
        $request = Request::create('/api/v1/test', 'GET');
        $response = ApiExceptionRenderer::render(new AuthenticationException, $request);

        $this->assertSame(401, $response?->getStatusCode());
    }

    #[Test]
    public function render_maps_authorization_exception_to_403(): void
    {
        $request = Request::create('/api/v1/test', 'GET');
        $response = ApiExceptionRenderer::render(new AuthorizationException('Denied.'), $request);

        $this->assertSame(403, $response?->getStatusCode());
        $this->assertSame('Denied.', $response?->getData(true)['message']);
    }

    #[Test]
    public function render_maps_model_not_found_to_404(): void
    {
        $request = Request::create('/api/v1/test', 'GET');
        $response = ApiExceptionRenderer::render(new ModelNotFoundException, $request);

        $this->assertSame(404, $response?->getStatusCode());
        $this->assertSame('Resource not found.', $response?->getData(true)['message']);
    }
}

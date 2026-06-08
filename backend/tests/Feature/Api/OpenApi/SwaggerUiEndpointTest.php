<?php

declare(strict_types=1);

namespace Tests\Feature\Api\OpenApi;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SwaggerUiEndpointTest extends TestCase
{
    #[Test]
    public function swagger_ui_endpoint_renders_documentation_page(): void
    {
        $response = $this->get('/api/docs');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/html; charset=utf-8');
        $response->assertSee('swagger-ui', false);
        $response->assertSee('/api/openapi.yaml', false);
        $response->assertSee('Script Manager API', false);
    }

    #[Test]
    public function swagger_ui_can_be_disabled(): void
    {
        config(['openapi.ui_enabled' => false]);

        $response = $this->get('/api/docs');

        $response->assertNotFound();
        $response->assertJson([
            'success' => false,
            'message' => 'Resource not found.',
        ]);
    }
}

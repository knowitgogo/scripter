<?php

declare(strict_types=1);

namespace Tests\Feature\Api\OpenApi;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class OpenApiSpecEndpointTest extends TestCase
{
    #[Test]
    public function openapi_spec_endpoint_returns_yaml_document(): void
    {
        $response = $this->get('/api/openapi.yaml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/yaml; charset=utf-8');
        $this->assertStringContainsString('openapi: 3.1.0', (string) $response->getContent());
        $this->assertStringContainsString('/health:', (string) $response->getContent());
    }
}

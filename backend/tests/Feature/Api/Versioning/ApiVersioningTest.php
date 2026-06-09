<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Versioning;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ApiVersioningTest extends TestCase
{
    #[Test]
    public function versioned_routes_are_registered_under_api_v1_prefix(): void
    {
        $this->assertNotNull(route('api.v1.health'));
        $this->assertNotNull(route('api.v1.ready'));
        $this->assertStringContainsString('/api/v1/health', route('api.v1.health'));
    }

    #[Test]
    public function versioned_responses_include_api_version_header(): void
    {
        config(['api.version_header' => 'X-API-Version']);

        $response = $this->getJson('/api/v1/health');

        $response->assertOk();
        $response->assertHeader('X-API-Version', 'v1');
    }

    #[Test]
    public function unsupported_api_version_returns_not_found_envelope(): void
    {
        $response = $this->getJson('/api/v99/health');

        $response->assertNotFound();
        $response->assertJson([
            'success' => false,
            'message' => 'Resource not found.',
            'errors' => [],
        ]);
    }

    #[Test]
    public function unversioned_meta_routes_remain_available(): void
    {
        $this->get('/api/openapi.yaml')->assertOk();
        $this->get('/api/docs')->assertOk();
    }
}

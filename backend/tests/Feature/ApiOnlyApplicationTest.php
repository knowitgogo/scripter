<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ApiOnlyApplicationTest extends TestCase
{
    #[Test]
    public function root_route_returns_json_not_found_envelope(): void
    {
        $response = $this->getJson('/');

        $response->assertNotFound();
        $response->assertJson([
            'success' => false,
            'message' => 'Resource not found.',
            'errors' => [],
        ]);
    }

    #[Test]
    public function health_probe_remains_available(): void
    {
        $response = $this->get('/up');

        $response->assertOk();
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Repositories\Contracts\InfrastructureProbeRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class HealthEndpointTest extends TestCase
{
    #[Test]
    public function health_endpoint_returns_liveness_envelope(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => ['status', 'version'],
            'message',
            'errors',
        ]);
        $response->assertJson([
            'success' => true,
            'data' => [
                'status' => 'ok',
                'version' => 'v1',
            ],
            'errors' => [],
        ]);
    }
}

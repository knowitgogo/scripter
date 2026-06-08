<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Repositories\Contracts\InfrastructureProbeRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ReadinessEndpointTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function readiness_endpoint_returns_ready_when_dependencies_available(): void
    {
        $response = $this->getJson('/api/v1/ready');

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'status',
                'checks' => ['database', 'cache'],
                'version',
            ],
            'message',
            'errors',
        ]);
        $response->assertJson([
            'success' => true,
            'data' => [
                'status' => 'ready',
                'checks' => [
                    'database' => 'ok',
                    'cache' => 'ok',
                ],
                'version' => 'v1',
            ],
            'errors' => [],
        ]);
    }

    #[Test]
    public function readiness_endpoint_returns_503_when_not_ready(): void
    {
        $this->mock(InfrastructureProbeRepositoryInterface::class, function ($mock): void {
            $mock->shouldReceive('isDatabaseReachable')->andReturn(false);
            $mock->shouldReceive('isCacheReachable')->andReturn(true);
        });

        $response = $this->getJson('/api/v1/ready');

        $response->assertStatus(503);
        $response->assertJson([
            'success' => true,
            'data' => [
                'status' => 'not_ready',
                'checks' => [
                    'database' => 'fail',
                    'cache' => 'ok',
                ],
                'version' => 'v1',
            ],
        ]);
    }
}

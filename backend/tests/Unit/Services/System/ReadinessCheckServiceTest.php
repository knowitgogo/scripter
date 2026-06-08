<?php

declare(strict_types=1);

namespace Tests\Unit\Services\System;

use App\DTOs\System\ReadinessStatusDTO;
use App\Repositories\Contracts\InfrastructureProbeRepositoryInterface;
use App\Services\System\ReadinessCheckService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ReadinessCheckServiceTest extends TestCase
{
    #[Test]
    public function it_returns_ready_when_all_checks_pass(): void
    {
        $probes = $this->createMock(InfrastructureProbeRepositoryInterface::class);
        $probes->method('isDatabaseReachable')->willReturn(true);
        $probes->method('isCacheReachable')->willReturn(true);

        $status = (new ReadinessCheckService($probes))->check();

        $this->assertInstanceOf(ReadinessStatusDTO::class, $status);
        $this->assertTrue($status->isReady());
        $this->assertSame([
            'status' => 'ready',
            'checks' => [
                'database' => 'ok',
                'cache' => 'ok',
            ],
            'version' => 'v1',
        ], $status->toArray());
    }

    #[Test]
    public function it_returns_not_ready_when_database_check_fails(): void
    {
        $probes = $this->createMock(InfrastructureProbeRepositoryInterface::class);
        $probes->method('isDatabaseReachable')->willReturn(false);
        $probes->method('isCacheReachable')->willReturn(true);

        $status = (new ReadinessCheckService($probes))->check();

        $this->assertFalse($status->isReady());
        $this->assertSame('fail', $status->checks['database']);
    }

    #[Test]
    public function it_returns_not_ready_when_cache_check_fails(): void
    {
        $probes = $this->createMock(InfrastructureProbeRepositoryInterface::class);
        $probes->method('isDatabaseReachable')->willReturn(true);
        $probes->method('isCacheReachable')->willReturn(false);

        $status = (new ReadinessCheckService($probes))->check();

        $this->assertFalse($status->isReady());
        $this->assertSame('fail', $status->checks['cache']);
    }
}

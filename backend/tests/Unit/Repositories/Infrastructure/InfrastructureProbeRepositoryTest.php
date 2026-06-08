<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Infrastructure;

use App\Repositories\Infrastructure\InfrastructureProbeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class InfrastructureProbeRepositoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function database_probe_succeeds_when_connection_available(): void
    {
        $repository = new InfrastructureProbeRepository;

        $this->assertTrue($repository->isDatabaseReachable());
    }

    #[Test]
    public function database_probe_fails_when_connection_unavailable(): void
    {
        DB::shouldReceive('connection')->andThrow(new \RuntimeException('Connection refused'));

        $repository = new InfrastructureProbeRepository;

        $this->assertFalse($repository->isDatabaseReachable());
    }

    #[Test]
    public function cache_probe_succeeds_when_cache_available(): void
    {
        $repository = new InfrastructureProbeRepository;

        $this->assertTrue($repository->isCacheReachable());
    }

    #[Test]
    public function cache_probe_fails_when_cache_unavailable(): void
    {
        Cache::shouldReceive('put')->andThrow(new \RuntimeException('Cache unavailable'));

        $repository = new InfrastructureProbeRepository;

        $this->assertFalse($repository->isCacheReachable());
    }
}

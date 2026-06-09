<?php

declare(strict_types=1);

namespace App\Services\System;

use App\DTOs\System\ReadinessStatusDTO;
use App\Repositories\Contracts\InfrastructureProbeRepositoryInterface;
use App\Support\ApiVersion;

/**
 * Readiness check — verifies infrastructure dependencies are available.
 */
final class ReadinessCheckService
{
    public function __construct(
        private readonly InfrastructureProbeRepositoryInterface $probes,
    ) {}

    public function check(?string $version = null): ReadinessStatusDTO
    {
        $checks = [
            'database' => $this->probes->isDatabaseReachable() ? 'ok' : 'fail',
            'cache' => $this->probes->isCacheReachable() ? 'ok' : 'fail',
        ];

        if (config('infrastructure.redis.enabled')) {
            $checks['redis'] = $this->probes->isRedisReachable() ? 'ok' : 'fail';
        }

        return ReadinessStatusDTO::fromChecks($checks, $version ?? ApiVersion::default());
    }
}

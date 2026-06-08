<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

/**
 * Probes infrastructure dependencies for readiness checks.
 */
interface InfrastructureProbeRepositoryInterface extends RepositoryInterface
{
    public function isDatabaseReachable(): bool;

    public function isCacheReachable(): bool;
}

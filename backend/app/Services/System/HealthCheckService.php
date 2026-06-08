<?php

declare(strict_types=1);

namespace App\Services\System;

use App\DTOs\System\HealthStatusDTO;

/**
 * Liveness check — no external dependencies are queried.
 */
final class HealthCheckService
{
    public function check(string $version = 'v1'): HealthStatusDTO
    {
        return HealthStatusDTO::alive($version);
    }
}

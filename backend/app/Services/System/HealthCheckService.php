<?php

declare(strict_types=1);

namespace App\Services\System;

use App\DTOs\System\HealthStatusDTO;
use App\Support\ApiVersion;

/**
 * Liveness check — no external dependencies are queried.
 */
final class HealthCheckService
{
    public function check(?string $version = null): HealthStatusDTO
    {
        return HealthStatusDTO::alive($version ?? ApiVersion::default());
    }
}

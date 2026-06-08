<?php

declare(strict_types=1);

namespace App\DTOs\System;

use App\DTOs\DataTransferObject;

/**
 * Liveness probe result — confirms the application process is running.
 */
final class HealthStatusDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $status = 'ok',
        public readonly string $version = 'v1',
    ) {}

    public static function alive(string $version = 'v1'): self
    {
        return new self(status: 'ok', version: $version);
    }
}

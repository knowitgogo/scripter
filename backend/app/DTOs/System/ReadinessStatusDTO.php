<?php

declare(strict_types=1);

namespace App\DTOs\System;

use App\DTOs\DataTransferObject;

/**
 * Readiness probe result — confirms dependencies required to serve traffic.
 */
final class ReadinessStatusDTO extends DataTransferObject
{
    /**
     * @param  array<string, string>  $checks
     */
    public function __construct(
        public readonly string $status,
        public readonly array $checks,
        public readonly string $version = 'v1',
    ) {}

    /**
     * @param  array<string, string>  $checks
     */
    public static function fromChecks(array $checks, string $version = 'v1'): self
    {
        $isReady = collect($checks)->every(
            static fn (string $state): bool => in_array($state, ['ok', 'skipped'], true),
        );

        return new self(
            status: $isReady ? 'ready' : 'not_ready',
            checks: $checks,
            version: $version,
        );
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }
}

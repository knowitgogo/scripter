<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\Audit\Contracts\AuditEventInterface;
use App\Services\Audit\AuditLogService;

/**
 * Thin listener that delegates audit persistence to AuditLogService.
 */
final class RecordAuditLog
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function handle(AuditEventInterface $event): void
    {
        $this->auditLogService->record($event);
    }
}

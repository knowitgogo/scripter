<?php

declare(strict_types=1);

namespace App\Jobs;

use App\DTOs\Audit\AuditLogEntryDTO;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Asynchronously persists an audit log entry.
 */
final class PersistAuditLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly AuditLogEntryDTO $entry,
    ) {}

    public function handle(AuditLogRepositoryInterface $auditLogs): void
    {
        $auditLogs->store($this->entry);
    }
}

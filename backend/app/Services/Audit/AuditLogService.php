<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\DTOs\Audit\AuditLogEntryDTO;
use App\Events\Audit\Contracts\AuditEventInterface;
use App\Jobs\PersistAuditLogJob;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Services\Infrastructure\QueueService;

/**
 * Records audit events synchronously or via queue.
 */
final class AuditLogService
{
    public function __construct(
        private readonly AuditLogRepositoryInterface $auditLogs,
        private readonly QueueService $queue,
    ) {}

    public function record(AuditEventInterface $event): void
    {
        if (! config('audit.enabled')) {
            return;
        }

        $entry = $event->toEntry();

        if (config('audit.async')) {
            $this->queue->dispatch(new PersistAuditLogJob($entry), (string) config('audit.queue', 'default'));

            return;
        }

        $this->persist($entry);
    }

    public function persist(AuditLogEntryDTO $entry): void
    {
        $this->auditLogs->store($entry);
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Audit;

use App\DTOs\Audit\AuditLogEntryDTO;
use App\Models\AuditLog;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Repositories\Eloquent\UuidEloquentRepository;

/**
 * Eloquent persistence for audit log entries.
 */
final class EloquentAuditLogRepository extends UuidEloquentRepository implements AuditLogRepositoryInterface
{
    public function store(AuditLogEntryDTO $entry): AuditLog
    {
        /** @var AuditLog $auditLog */
        $auditLog = $this->newModelQuery()->create($entry->toPersistenceArray());

        return $auditLog;
    }

    protected function model(): string
    {
        return AuditLog::class;
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\Audit\AuditLogEntryDTO;
use App\Models\AuditLog;

/**
 * Persists audit log entries.
 */
interface AuditLogRepositoryInterface extends RepositoryInterface
{
    public function store(AuditLogEntryDTO $entry): AuditLog;
}

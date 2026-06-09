<?php

declare(strict_types=1);

namespace App\Events\Audit\Contracts;

use App\DTOs\Audit\AuditLogEntryDTO;
use App\Enums\AuditAction;

/**
 * Contract for immutable audit events dispatched by domain Services.
 */
interface AuditEventInterface
{
    public function action(): AuditAction;

    public function subjectType(): string;

    public function subjectUuid(): ?string;

    public function actorUuid(): ?string;

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array;

    public function ipAddress(): ?string;

    public function userAgent(): ?string;

    public function toEntry(): AuditLogEntryDTO;
}

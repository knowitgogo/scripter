<?php

declare(strict_types=1);

namespace App\Events\Audit;

use App\DTOs\Audit\AuditLogEntryDTO;
use App\Enums\AuditAction;
use App\Events\Audit\Contracts\AuditEventInterface;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Base immutable audit event. Domain modules extend or compose this class.
 */
abstract class AbstractAuditEvent implements AuditEventInterface
{
    use Dispatchable, SerializesModels;

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        protected readonly AuditAction $action,
        protected readonly string $subjectType,
        protected readonly ?string $subjectUuid = null,
        protected readonly ?string $actorUuid = null,
        protected readonly array $metadata = [],
        protected readonly ?string $ipAddress = null,
        protected readonly ?string $userAgent = null,
    ) {}

    public function action(): AuditAction
    {
        return $this->action;
    }

    public function subjectType(): string
    {
        return $this->subjectType;
    }

    public function subjectUuid(): ?string
    {
        return $this->subjectUuid;
    }

    public function actorUuid(): ?string
    {
        return $this->actorUuid;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }

    public function ipAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function userAgent(): ?string
    {
        return $this->userAgent;
    }

    public function toEntry(): AuditLogEntryDTO
    {
        return AuditLogEntryDTO::fromEvent($this);
    }
}

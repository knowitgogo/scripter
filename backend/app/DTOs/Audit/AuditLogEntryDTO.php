<?php

declare(strict_types=1);

namespace App\DTOs\Audit;

use App\DTOs\DataTransferObject;
use App\Enums\AuditAction;
use App\Events\Audit\Contracts\AuditEventInterface;

/**
 * Immutable audit log entry passed to the persistence layer.
 */
final class AuditLogEntryDTO extends DataTransferObject
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly AuditAction $action,
        public readonly string $subjectType,
        public readonly ?string $subjectUuid,
        public readonly ?string $actorUuid,
        public readonly array $metadata,
        public readonly ?string $ipAddress,
        public readonly ?string $userAgent,
    ) {}

    public static function fromEvent(AuditEventInterface $event): self
    {
        return new self(
            action: $event->action(),
            subjectType: $event->subjectType(),
            subjectUuid: $event->subjectUuid(),
            actorUuid: $event->actorUuid(),
            metadata: $event->metadata(),
            ipAddress: $event->ipAddress(),
            userAgent: $event->userAgent(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistenceArray(): array
    {
        return [
            'action' => $this->action->value,
            'subject_type' => $this->subjectType,
            'subject_uuid' => $this->subjectUuid,
            'actor_uuid' => $this->actorUuid,
            'metadata' => $this->metadata,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
        ];
    }
}

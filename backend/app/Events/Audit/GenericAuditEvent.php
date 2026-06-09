<?php

declare(strict_types=1);

namespace App\Events\Audit;

use App\Enums\AuditAction;

/**
 * Generic audit event for domain Services until specialized events exist.
 */
final class GenericAuditEvent extends AbstractAuditEvent
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public static function record(
        AuditAction $action,
        string $subjectType,
        ?string $subjectUuid = null,
        ?string $actorUuid = null,
        array $metadata = [],
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): self {
        return new self(
            action: $action,
            subjectType: $subjectType,
            subjectUuid: $subjectUuid,
            actorUuid: $actorUuid,
            metadata: $metadata,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
        );
    }
}

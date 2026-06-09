<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs\Audit;

use App\DTOs\Audit\AuditLogEntryDTO;
use App\Enums\AuditAction;
use App\Events\Audit\GenericAuditEvent;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class AuditLogEntryDTOTest extends TestCase
{
    #[Test]
    public function it_maps_event_to_persistence_array(): void
    {
        $event = GenericAuditEvent::record(
            action: AuditAction::Created,
            subjectType: 'website',
            subjectUuid: '550e8400-e29b-41d4-a716-446655440000',
            actorUuid: '660e8400-e29b-41d4-a716-446655440001',
            metadata: ['name' => 'Acme'],
            ipAddress: '127.0.0.1',
            userAgent: 'PHPUnit',
        );

        $entry = AuditLogEntryDTO::fromEvent($event);

        $this->assertSame([
            'action' => 'created',
            'subject_type' => 'website',
            'subject_uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'actor_uuid' => '660e8400-e29b-41d4-a716-446655440001',
            'metadata' => ['name' => 'Acme'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ], $entry->toPersistenceArray());
    }
}

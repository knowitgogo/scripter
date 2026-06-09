<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\DTOs\Audit\AuditLogEntryDTO;
use App\Enums\AuditAction;
use App\Jobs\PersistAuditLogJob;
use App\Repositories\Audit\EloquentAuditLogRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PersistAuditLogJobTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_persists_entry_via_repository(): void
    {
        $entry = new AuditLogEntryDTO(
            action: AuditAction::Created,
            subjectType: 'website',
            subjectUuid: '550e8400-e29b-41d4-a716-446655440000',
            actorUuid: null,
            metadata: [],
            ipAddress: null,
            userAgent: null,
        );

        (new PersistAuditLogJob($entry))->handle(new EloquentAuditLogRepository);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'subject_type' => 'website',
            'subject_uuid' => '550e8400-e29b-41d4-a716-446655440000',
        ]);
    }
}

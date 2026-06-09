<?php

declare(strict_types=1);

namespace Tests\Feature\Audit;

use App\Enums\AuditAction;
use App\Events\Audit\GenericAuditEvent;
use App\Jobs\PersistAuditLogJob;
use App\Models\AuditLog;
use App\Services\Audit\AuditDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class AuditEventFlowTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_persists_audit_entry_synchronously_through_event_bus(): void
    {
        config(['audit.enabled' => true, 'audit.async' => false]);

        app(AuditDispatcher::class)->dispatch(
            GenericAuditEvent::record(
                action: AuditAction::Created,
                subjectType: 'website',
                subjectUuid: '550e8400-e29b-41d4-a716-446655440000',
                actorUuid: '660e8400-e29b-41d4-a716-446655440001',
                metadata: ['name' => 'Acme'],
            ),
        );

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'subject_type' => 'website',
            'subject_uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'actor_uuid' => '660e8400-e29b-41d4-a716-446655440001',
        ]);

        $auditLog = AuditLog::query()->first();
        $this->assertNotEmpty($auditLog->uuid);
        $this->assertArrayNotHasKey('id', $auditLog->toArray());
    }

    #[Test]
    public function it_queues_audit_entry_when_async_enabled(): void
    {
        Queue::fake();
        config(['audit.enabled' => true, 'audit.async' => true]);

        app(AuditDispatcher::class)->dispatch(
            GenericAuditEvent::record(AuditAction::Updated, 'widget'),
        );

        Queue::assertPushed(PersistAuditLogJob::class);
        $this->assertDatabaseCount('audit_logs', 0);
    }
}

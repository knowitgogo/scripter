<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Audit;

use App\Enums\AuditAction;
use App\Events\Audit\GenericAuditEvent;
use App\Jobs\PersistAuditLogJob;
use App\Repositories\Audit\EloquentAuditLogRepository;
use App\Repositories\Contracts\QueueDispatcherInterface;
use App\Services\Audit\AuditLogService;
use App\Services\Infrastructure\QueueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class AuditLogServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_persists_synchronously_when_async_disabled(): void
    {
        config(['audit.enabled' => true, 'audit.async' => false]);

        $service = new AuditLogService(
            new EloquentAuditLogRepository,
            new QueueService($this->createMock(QueueDispatcherInterface::class)),
        );

        $service->record(GenericAuditEvent::record(
            AuditAction::Created,
            'website',
            '550e8400-e29b-41d4-a716-446655440000',
        ));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'subject_type' => 'website',
        ]);
    }

    #[Test]
    public function it_dispatches_job_when_async_enabled(): void
    {
        Queue::fake();
        config(['audit.enabled' => true, 'audit.async' => true, 'audit.queue' => 'default']);

        $queueDispatcher = $this->createMock(QueueDispatcherInterface::class);
        $queueDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(PersistAuditLogJob::class), 'default');

        $service = new AuditLogService(
            new EloquentAuditLogRepository,
            new QueueService($queueDispatcher),
        );

        $service->record(GenericAuditEvent::record(
            AuditAction::Updated,
            'widget',
        ));
    }

    #[Test]
    public function it_skips_recording_when_audit_disabled(): void
    {
        config(['audit.enabled' => false, 'audit.async' => false]);

        $service = new AuditLogService(
            new EloquentAuditLogRepository,
            new QueueService($this->createMock(QueueDispatcherInterface::class)),
        );

        $service->record(GenericAuditEvent::record(
            AuditAction::Deleted,
            'user',
        ));

        $this->assertDatabaseCount('audit_logs', 0);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners;

use App\Enums\AuditAction;
use App\Events\Audit\GenericAuditEvent;
use App\Listeners\RecordAuditLog;
use App\Repositories\Audit\EloquentAuditLogRepository;
use App\Repositories\Contracts\QueueDispatcherInterface;
use App\Services\Audit\AuditLogService;
use App\Services\Infrastructure\QueueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RecordAuditLogTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_delegates_to_audit_log_service(): void
    {
        config(['audit.enabled' => true, 'audit.async' => false]);

        $event = GenericAuditEvent::record(AuditAction::Created, 'website');

        $service = new AuditLogService(
            new EloquentAuditLogRepository,
            new QueueService($this->createMock(QueueDispatcherInterface::class)),
        );

        (new RecordAuditLog($service))->handle($event);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'subject_type' => 'website',
        ]);
    }
}

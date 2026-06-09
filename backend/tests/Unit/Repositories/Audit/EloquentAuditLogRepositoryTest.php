<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Audit;

use App\DTOs\Audit\AuditLogEntryDTO;
use App\Enums\AuditAction;
use App\Repositories\Audit\EloquentAuditLogRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EloquentAuditLogRepositoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_audit_log_with_uuid(): void
    {
        $repository = new EloquentAuditLogRepository;

        $auditLog = $repository->store(new AuditLogEntryDTO(
            action: AuditAction::Authenticated,
            subjectType: 'user',
            subjectUuid: '550e8400-e29b-41d4-a716-446655440000',
            actorUuid: '550e8400-e29b-41d4-a716-446655440000',
            metadata: ['method' => 'jwt'],
            ipAddress: '10.0.0.1',
            userAgent: 'curl',
        ));

        $this->assertNotEmpty($auditLog->uuid);
        $this->assertSame('authenticated', $auditLog->action);
        $this->assertSame(['method' => 'jwt'], $auditLog->metadata);
    }
}

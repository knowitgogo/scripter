<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Infrastructure;

use App\Repositories\Contracts\QueueDispatcherInterface;
use App\Services\Infrastructure\QueueService;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\DispatchProbeJob;
use Tests\TestCase;

final class QueueServiceTest extends TestCase
{
    #[Test]
    public function it_dispatches_to_analytics_queue(): void
    {
        $dispatcher = $this->createMock(QueueDispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(DispatchProbeJob::class), 'analytics');

        (new QueueService($dispatcher))->dispatchAnalytics(new DispatchProbeJob);
    }

    #[Test]
    public function it_dispatches_to_billing_queue(): void
    {
        $dispatcher = $this->createMock(QueueDispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(DispatchProbeJob::class), 'billing');

        (new QueueService($dispatcher))->dispatchBilling(new DispatchProbeJob);
    }
}

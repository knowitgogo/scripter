<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Queue;

use App\Repositories\Queue\LaravelQueueDispatcher;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\DispatchProbeJob;
use Tests\TestCase;

final class LaravelQueueDispatcherTest extends TestCase
{
    #[Test]
    public function it_dispatches_jobs_to_named_queues(): void
    {
        Queue::fake();

        $dispatcher = new LaravelQueueDispatcher;

        $dispatcher->dispatch(new DispatchProbeJob, 'analytics');

        Queue::assertPushedOn('analytics', DispatchProbeJob::class);
    }

    #[Test]
    public function it_reports_configured_connection(): void
    {
        config(['infrastructure.queue.connection' => 'sync']);

        $dispatcher = new LaravelQueueDispatcher;

        $this->assertSame('sync', $dispatcher->connection());
    }
}

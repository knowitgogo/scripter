<?php

declare(strict_types=1);

namespace App\Services\Infrastructure;

use App\Repositories\Contracts\QueueDispatcherInterface;

/**
 * Dispatches jobs to named platform queues.
 */
final class QueueService
{
    public function __construct(
        private readonly QueueDispatcherInterface $dispatcher,
    ) {}

    public function dispatchDefault(object $job): void
    {
        $this->dispatch($job, 'default');
    }

    public function dispatchAnalytics(object $job): void
    {
        $this->dispatch($job, 'analytics');
    }

    public function dispatchBilling(object $job): void
    {
        $this->dispatch($job, 'billing');
    }

    public function dispatch(object $job, string $queueKey): void
    {
        $queueName = (string) config("infrastructure.queue.names.{$queueKey}", $queueKey);

        $this->dispatcher->dispatch($job, $queueName);
    }
}

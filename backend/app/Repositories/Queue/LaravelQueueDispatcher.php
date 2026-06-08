<?php

declare(strict_types=1);

namespace App\Repositories\Queue;

use App\Repositories\Contracts\QueueDispatcherInterface;
use Illuminate\Support\Facades\Queue;

/**
 * Laravel queue connection adapter.
 */
final class LaravelQueueDispatcher implements QueueDispatcherInterface
{
    public function dispatch(object $job, string $queue, ?string $connection = null): void
    {
        $connectionName = $connection ?? $this->connection();

        Queue::connection($connectionName)->pushOn($queue, $job);
    }

    public function connection(): string
    {
        return (string) config('infrastructure.queue.connection');
    }
}

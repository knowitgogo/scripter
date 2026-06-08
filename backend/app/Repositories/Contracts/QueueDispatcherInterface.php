<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

/**
 * Queue dispatch abstraction. Services use this instead of the Queue facade.
 */
interface QueueDispatcherInterface extends RepositoryInterface
{
    public function dispatch(object $job, string $queue, ?string $connection = null): void;

    public function connection(): string;
}

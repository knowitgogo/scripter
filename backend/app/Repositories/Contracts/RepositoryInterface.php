<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

/**
 * Root marker for repository implementations.
 *
 * Domain persistence repositories extend {@see EloquentRepositoryInterface}
 * or {@see UuidRepositoryInterface}. Infrastructure repositories (cache,
 * queue, probes) extend this interface directly.
 */
interface RepositoryInterface
{
}

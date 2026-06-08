<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Repository contract for aggregates addressed by public UUID.
 */
interface UuidRepositoryInterface extends RepositoryInterface
{
    public function findByUuid(string $uuid): ?Model;

    public function findByUuidOrFail(string $uuid): Model;
}

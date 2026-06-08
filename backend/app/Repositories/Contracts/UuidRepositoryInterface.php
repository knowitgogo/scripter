<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Repository contract for public entities addressed by UUID.
 *
 * @extends EloquentRepositoryInterface
 */
interface UuidRepositoryInterface extends EloquentRepositoryInterface
{
    public function findByUuid(string $uuid): ?Model;

    public function findByUuidOrFail(string $uuid): Model;
}

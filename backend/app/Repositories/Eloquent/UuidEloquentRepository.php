<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Repositories\Concerns\FindsByUuid;
use App\Repositories\Contracts\UuidRepositoryInterface;

/**
 * Base repository for {@see \App\Models\PublicEntity} aggregates with UUID public identifiers.
 */
abstract class UuidEloquentRepository extends EloquentRepository implements UuidRepositoryInterface
{
    use FindsByUuid;
}

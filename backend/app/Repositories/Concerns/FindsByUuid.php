<?php

declare(strict_types=1);

namespace App\Repositories\Concerns;

use App\Repositories\Eloquent\EloquentRepository;
use App\Support\UuidGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * UUID lookup helpers for Eloquent repositories.
 *
 * @mixin EloquentRepository
 */
trait FindsByUuid
{
    public function findByUuid(string $uuid): ?Model
    {
        if (! UuidGenerator::isValid($uuid)) {
            return null;
        }

        return $this->newModelQuery()->whereUuid($uuid)->first();
    }

    public function findByUuidOrFail(string $uuid): Model
    {
        $model = $this->findByUuid($uuid);

        if ($model === null) {
            throw (new ModelNotFoundException)->setModel($this->model());
        }

        return $model;
    }
}

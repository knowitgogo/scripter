<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Base contract for Eloquent-backed aggregate repositories.
 *
 * Repositories return Models internally. Services map Models to DTOs before
 * crossing layer boundaries.
 */
interface EloquentRepositoryInterface extends RepositoryInterface
{
    public function findById(int $id): ?Model;

    public function findByIdOrFail(int $id): Model;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Model;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Model $model, array $attributes): bool;

    public function delete(Model $model): bool;

    public function deleteById(int $id): bool;
}

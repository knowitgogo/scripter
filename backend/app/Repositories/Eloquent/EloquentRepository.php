<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\EloquentRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Base Eloquent repository with standard CRUD operations.
 *
 * Domain repositories extend this class or {@see UuidEloquentRepository}.
 */
abstract class EloquentRepository implements EloquentRepositoryInterface
{
    /**
     * @return class-string<Model>
     */
    abstract protected function model(): string;

    public function findById(int $id): ?Model
    {
        return $this->newModelQuery()->find($id);
    }

    public function findByIdOrFail(int $id): Model
    {
        return $this->newModelQuery()->findOrFail($id);
    }

    public function create(array $attributes): Model
    {
        return $this->newModelQuery()->create($attributes);
    }

    public function update(Model $model, array $attributes): bool
    {
        return $model->update($attributes);
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    public function deleteById(int $id): bool
    {
        return $this->newModelQuery()->whereKey($id)->delete() > 0;
    }

    /**
     * @return Builder<Model>
     */
    protected function newModelQuery(): Builder
    {
        $model = $this->model();

        return $model::query();
    }
}

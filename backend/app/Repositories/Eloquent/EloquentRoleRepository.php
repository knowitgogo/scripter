<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\RoleSlug;
use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Eloquent persistence for role aggregates.
 */
final class EloquentRoleRepository extends UuidEloquentRepository implements RoleRepositoryInterface
{
    public function findBySlug(RoleSlug $slug): ?Role
    {
        /** @var Role|null $role */
        $role = $this->newModelQuery()->where('slug', $slug->value)->first();

        return $role;
    }

    public function findBySlugOrFail(RoleSlug $slug): Role
    {
        $role = $this->findBySlug($slug);

        if ($role === null) {
            throw (new ModelNotFoundException)->setModel($this->model());
        }

        return $role;
    }

    protected function model(): string
    {
        return Role::class;
    }
}

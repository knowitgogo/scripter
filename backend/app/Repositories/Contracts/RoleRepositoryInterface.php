<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Enums\RoleSlug;
use App\Models\Role;

/**
 * Repository contract for {@see \App\Models\Role} aggregates.
 */
interface RoleRepositoryInterface extends UuidRepositoryInterface
{
    public function findBySlug(RoleSlug $slug): ?Role;

    public function findBySlugOrFail(RoleSlug $slug): Role;
}

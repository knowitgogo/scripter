<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Enums\RoleSlug;

/**
 * Resolves permission identifiers granted to a role.
 */
interface PermissionsRepositoryInterface extends RepositoryInterface
{
    /**
     * @return list<string>
     */
    public function forRole(RoleSlug $role): array;
}

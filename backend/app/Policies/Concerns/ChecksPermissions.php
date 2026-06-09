<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

use App\Enums\Permission;
use App\Enums\RoleSlug;
use App\Models\User;
use App\Services\Auth\PermissionService;

/**
 * Permission helpers for policy classes.
 */
trait ChecksPermissions
{
    abstract protected function permissionService(): PermissionService;

    protected function allows(User $user, Permission $permission): bool
    {
        return $this->permissionService()->userHasPermission($user, $permission);
    }

    protected function hasRole(User $user, RoleSlug ...$roles): bool
    {
        return $this->permissionService()->userHasRole($user, ...$roles);
    }
}

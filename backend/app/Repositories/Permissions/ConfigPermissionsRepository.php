<?php

declare(strict_types=1);

namespace App\Repositories\Permissions;

use App\Enums\Permission;
use App\Enums\RoleSlug;
use App\Repositories\Contracts\PermissionsRepositoryInterface;

/**
 * Loads role permissions from {@see config('permissions.roles')}.
 */
final class ConfigPermissionsRepository implements PermissionsRepositoryInterface
{
    /**
     * @return list<string>
     */
    public function forRole(RoleSlug $role): array
    {
        /** @var list<string> $configured */
        $configured = config('permissions.roles.'.$role->value, []);

        if (in_array('*', $configured, true)) {
            return Permission::values();
        }

        return array_values(array_unique($configured));
    }
}

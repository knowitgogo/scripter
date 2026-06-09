<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\DTOs\DataTransferObject;
use App\Enums\Permission;
use App\Enums\RoleSlug;
use BackedEnum;

/**
 * Resolved permission set for an authenticated user.
 */
final class UserPermissionsDTO extends DataTransferObject
{
    /**
     * @param  list<string>  $permissions
     */
    public function __construct(
        public readonly string $user_uuid,
        public readonly RoleSlug $role,
        public readonly array $permissions,
    ) {}

    public function allows(Permission|string $permission): bool
    {
        $permissionValue = $permission instanceof Permission
            ? $permission->value
            : $permission;

        if (in_array('*', $this->permissions, true)) {
            return true;
        }

        return in_array($permissionValue, $this->permissions, true);
    }

    protected function transformValue(mixed $value): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        return parent::transformValue($value);
    }
}

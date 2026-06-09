<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\DTOs\Auth\UserPermissionsDTO;
use App\Enums\Permission;
use App\Enums\RoleSlug;
use App\Models\User;
use App\Repositories\Contracts\PermissionsRepositoryInterface;
use App\Services\Infrastructure\CacheService;

/**
 * Resolves and caches user permissions derived from role assignments.
 */
final class PermissionService
{
    public function __construct(
        private readonly PermissionsRepositoryInterface $permissions,
        private readonly CacheService $cache,
    ) {}

    public function forUser(User $user): UserPermissionsDTO
    {
        return $this->cache->remember(
            'user_permissions',
            ['user_uuid' => $user->uuid],
            fn (): UserPermissionsDTO => $this->resolveForUser($user),
        );
    }

    public function userHasPermission(User $user, Permission $permission): bool
    {
        return $this->forUser($user)->allows($permission);
    }

    public function userHasRole(User $user, RoleSlug ...$roles): bool
    {
        $user->loadMissing('role');

        if ($user->role === null) {
            return false;
        }

        foreach ($roles as $role) {
            if ($user->role->slug === $role) {
                return true;
            }
        }

        return false;
    }

    public function forgetUser(User $user): bool
    {
        return $this->cache->forget('user_permissions', ['user_uuid' => $user->uuid]);
    }

    private function resolveForUser(User $user): UserPermissionsDTO
    {
        $user->loadMissing('role');

        if ($user->role === null) {
            return new UserPermissionsDTO(
                user_uuid: $user->uuid,
                role: RoleSlug::Customer,
                permissions: [],
            );
        }

        return new UserPermissionsDTO(
            user_uuid: $user->uuid,
            role: $user->role->slug,
            permissions: $this->permissions->forRole($user->role->slug),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;

/**
 * Enforces permission-based authorization for authenticated users.
 */
final class AuthorizationService
{
    public function __construct(
        private readonly PermissionService $permissions,
    ) {}

    /**
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function authorizePermission(?User $user, Permission $permission): void
    {
        if ($user === null) {
            throw new AuthenticationException('Unauthenticated.');
        }

        if (! $this->permissions->userHasPermission($user, $permission)) {
            throw new AuthorizationException('Forbidden.');
        }
    }
}

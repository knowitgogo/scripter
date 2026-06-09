<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Permission;
use App\Models\User;
use App\Services\Auth\PermissionService;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user holds a specific permission.
 */
final class EnsurePermission
{
    public function __construct(
        private readonly PermissionService $permissions,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        /** @var User|null $user */
        $user = auth('api')->user();

        if ($user === null) {
            throw new AuthorizationException('Unauthenticated.');
        }

        if (! $this->permissions->userHasPermission($user, Permission::from($permission))) {
            throw new AuthorizationException('Forbidden.');
        }

        return $next($request);
    }
}

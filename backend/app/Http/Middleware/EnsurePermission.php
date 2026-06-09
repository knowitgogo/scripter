<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Permission;
use App\Models\User;
use App\Services\Auth\AuthorizationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user holds a specific permission.
 *
 * Register on routes as `permission:{permission_slug}` after `auth:api`.
 */
final class EnsurePermission
{
    public function __construct(
        private readonly AuthorizationService $authorization,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        /** @var User|null $user */
        $user = auth('api')->user();

        $this->authorization->authorizePermission($user, Permission::from($permission));

        return $next($request);
    }
}

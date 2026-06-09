<?php

declare(strict_types=1);

namespace App\Support\Auth;

use App\Enums\RoleSlug;
use App\Models\User;

/**
 * Builds JWT custom claims for authenticated users.
 */
final class JwtClaimBuilder
{
    /**
     * @return array<string, string>
     */
    public static function forUser(User $user): array
    {
        $user->loadMissing('role');

        $role = $user->role?->slug;

        return [
            'role' => ($role instanceof RoleSlug ? $role : RoleSlug::Customer)->value,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\DTOs\Auth\UserDTO;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Auth\AuthenticationException;

/**
 * Returns the authenticated user's public profile.
 */
final class CurrentUserService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    /**
     * @throws AuthenticationException
     */
    public function get(): UserDTO
    {
        /** @var User|null $authenticated */
        $authenticated = auth('api')->user();

        if ($authenticated === null) {
            throw new AuthenticationException('Unauthenticated.');
        }

        /** @var User $user */
        $user = $this->users->findByUuidOrFail($authenticated->uuid);
        $user->loadMissing('role');

        return UserDTO::fromModel($user);
    }
}

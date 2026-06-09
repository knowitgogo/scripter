<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Concerns\FindsByUuid;
use App\Repositories\Contracts\UserRepositoryInterface;

/**
 * Eloquent persistence for user aggregates.
 */
final class EloquentUserRepository extends EloquentRepository implements UserRepositoryInterface
{
    use FindsByUuid;

    public function findByEmail(string $email): ?User
    {
        /** @var User|null $user */
        $user = $this->newModelQuery()->where('email', $email)->first();

        return $user;
    }

    protected function model(): string
    {
        return User::class;
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;

/**
 * Repository contract for {@see \App\Models\User} aggregates.
 */
interface UserRepositoryInterface extends UuidRepositoryInterface
{
    public function findByEmail(string $email): ?User;
}

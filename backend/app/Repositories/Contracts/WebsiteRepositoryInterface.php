<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Website;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository contract for {@see \App\Models\Website} aggregates.
 */
interface WebsiteRepositoryInterface extends UuidRepositoryInterface
{
    public function findByUrl(string $url): ?Website;

    /**
     * @return Collection<int, Website>
     */
    public function listForUser(int $userId): Collection;

    public function findByUuidForUser(string $uuid, int $userId): ?Website;
}

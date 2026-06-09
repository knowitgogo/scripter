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
     * @param  list<int>  $tagIds  When non-empty, only websites tagged with all listed tags are returned.
     * @return Collection<int, Website>
     */
    public function listForUser(int $userId, array $tagIds = []): Collection;

    public function findByUuidForUser(string $uuid, int $userId): ?Website;
}

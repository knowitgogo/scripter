<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository contract for website–tag pivot persistence.
 */
interface WebsiteTagRepositoryInterface
{
    public function attach(int $websiteId, int $tagId): void;

    public function detach(int $websiteId, int $tagId): void;

    /**
     * @param  list<int>  $tagIds
     * @return array{attached: list<int>, detached: list<int>, updated: list<int>}
     */
    public function sync(int $websiteId, array $tagIds): array;

    /**
     * @return Collection<int, Tag>
     */
    public function listTagsForWebsite(int $websiteId): Collection;

    public function isAttached(int $websiteId, int $tagId): bool;
}

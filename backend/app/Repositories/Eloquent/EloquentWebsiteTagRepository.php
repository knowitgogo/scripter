<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Tag;
use App\Models\Website;
use App\Repositories\Contracts\WebsiteTagRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Eloquent persistence for website–tag pivot rows.
 */
final class EloquentWebsiteTagRepository implements WebsiteTagRepositoryInterface
{
    public function attach(int $websiteId, int $tagId): void
    {
        $website = $this->findWebsiteOrFail($websiteId);

        $website->tags()->syncWithoutDetaching([$tagId]);
    }

    public function detach(int $websiteId, int $tagId): void
    {
        $website = $this->findWebsiteOrFail($websiteId);

        $website->tags()->detach($tagId);
    }

    /**
     * @param  list<int>  $tagIds
     * @return array{attached: list<int>, detached: list<int>, updated: list<int>}
     */
    public function sync(int $websiteId, array $tagIds): array
    {
        $website = $this->findWebsiteOrFail($websiteId);

        /** @var array{attached: list<int>, detached: list<int>, updated: list<int>} $result */
        $result = $website->tags()->sync($tagIds);

        return $result;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function listTagsForWebsite(int $websiteId): Collection
    {
        $website = $this->findWebsiteOrFail($websiteId);

        return $website->tags()->orderBy('name')->get();
    }

    public function isAttached(int $websiteId, int $tagId): bool
    {
        $website = $this->findWebsiteOrFail($websiteId);

        return $website->tags()->where('tags.id', $tagId)->exists();
    }

    private function findWebsiteOrFail(int $websiteId): Website
    {
        /** @var Website|null $website */
        $website = Website::query()->find($websiteId);

        if ($website === null) {
            throw (new ModelNotFoundException)->setModel(Website::class);
        }

        return $website;
    }
}

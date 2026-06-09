<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Website;
use App\Repositories\Contracts\WebsiteRepositoryInterface;
use App\Support\UuidGenerator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Eloquent persistence for website aggregates.
 */
final class EloquentWebsiteRepository extends UuidEloquentRepository implements WebsiteRepositoryInterface
{
    public function findByUrl(string $url): ?Website
    {
        /** @var Website|null $website */
        $website = $this->newModelQuery()->where('url', $url)->first();

        return $website;
    }

    /**
     * @param  list<int>  $tagIds
     * @return Collection<int, Website>
     */
    public function listForUser(int $userId, array $tagIds = []): Collection
    {
        $query = $this->newModelQuery()->where('user_id', $userId);

        foreach ($tagIds as $tagId) {
            $query->whereHas('tags', fn ($builder) => $builder->where('tags.id', $tagId));
        }

        return $query->orderBy('name')->get();
    }

    public function findByUuidForUser(string $uuid, int $userId): ?Website
    {
        if (! UuidGenerator::isValid($uuid)) {
            return null;
        }

        /** @var Website|null $website */
        $website = $this->newModelQuery()
            ->whereUuid($uuid)
            ->where('user_id', $userId)
            ->first();

        return $website;
    }

    protected function model(): string
    {
        return Website::class;
    }
}

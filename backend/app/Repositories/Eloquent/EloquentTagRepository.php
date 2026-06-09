<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Tag;
use App\Repositories\Contracts\TagRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Eloquent persistence for tag aggregates.
 */
final class EloquentTagRepository extends UuidEloquentRepository implements TagRepositoryInterface
{
    public function findBySlug(string $slug): ?Tag
    {
        /** @var Tag|null $tag */
        $tag = $this->newModelQuery()->where('slug', $slug)->first();

        return $tag;
    }

    public function findBySlugOrFail(string $slug): Tag
    {
        $tag = $this->findBySlug($slug);

        if ($tag === null) {
            throw (new ModelNotFoundException)->setModel($this->model());
        }

        return $tag;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function listOrderedByName(): Collection
    {
        return $this->newModelQuery()->orderBy('name')->get();
    }

    protected function model(): string
    {
        return Tag::class;
    }
}

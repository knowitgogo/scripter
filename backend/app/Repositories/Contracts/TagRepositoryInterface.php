<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository contract for {@see \App\Models\Tag} aggregates.
 */
interface TagRepositoryInterface extends UuidRepositoryInterface
{
    public function findBySlug(string $slug): ?Tag;

    public function findBySlugOrFail(string $slug): Tag;

    /**
     * @return Collection<int, Tag>
     */
    public function listOrderedByName(): Collection;
}

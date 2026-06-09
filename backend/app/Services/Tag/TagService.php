<?php

declare(strict_types=1);

namespace App\Services\Tag;

use App\DTOs\Tag\TagDTO;
use App\Models\Tag;
use App\Repositories\Contracts\TagRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Resolves reusable tags for website labeling.
 */
final class TagService
{
    public function __construct(
        private readonly TagRepositoryInterface $tags,
    ) {}

    /**
     * @return list<TagDTO>
     */
    public function list(): array
    {
        return $this->tags->listOrderedByName()
            ->map(fn (Tag $tag): TagDTO => TagDTO::fromModel($tag))
            ->values()
            ->all();
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getByUuid(string $uuid): TagDTO
    {
        /** @var Tag $tag */
        $tag = $this->tags->findByUuidOrFail($uuid);

        return TagDTO::fromModel($tag);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getBySlug(string $slug): TagDTO
    {
        return TagDTO::fromModel($this->tags->findBySlugOrFail($slug));
    }
}

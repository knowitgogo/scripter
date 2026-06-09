<?php

declare(strict_types=1);

namespace App\Services\Tag;

use App\DTOs\Tag\TagDTO;
use App\DTOs\Website\SyncWebsiteTagsDTO;
use App\DTOs\Website\WebsiteTagsDTO;
use App\Models\Tag;
use App\Models\User;
use App\Models\Website;
use App\Repositories\Contracts\TagRepositoryInterface;
use App\Repositories\Contracts\WebsiteRepositoryInterface;
use App\Repositories\Contracts\WebsiteTagRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Manages reusable tags and their attachments to customer websites.
 */
final class TagService
{
    public function __construct(
        private readonly TagRepositoryInterface $tags,
        private readonly WebsiteTagRepositoryInterface $websiteTags,
        private readonly WebsiteRepositoryInterface $websites,
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

    /**
     * @return list<TagDTO>
     *
     * @throws ModelNotFoundException
     */
    public function listForWebsite(string $websiteUuid, User $user): array
    {
        $website = $this->resolveOwnedWebsite($websiteUuid, $user);

        return $this->mapTagsToDtos(
            $this->websiteTags->listTagsForWebsite($website->id),
        );
    }

    /**
     * @throws ModelNotFoundException
     */
    public function attach(string $websiteUuid, string $tagUuid, User $user): WebsiteTagsDTO
    {
        $website = $this->resolveOwnedWebsite($websiteUuid, $user);
        $tag = $this->tags->findByUuidOrFail($tagUuid);

        $this->websiteTags->attach($website->id, $tag->id);

        return WebsiteTagsDTO::forWebsite(
            $website->uuid,
            $this->mapTagsToDtos($this->websiteTags->listTagsForWebsite($website->id)),
        );
    }

    /**
     * @throws ModelNotFoundException
     */
    public function detach(string $websiteUuid, string $tagUuid, User $user): WebsiteTagsDTO
    {
        $website = $this->resolveOwnedWebsite($websiteUuid, $user);
        $tag = $this->tags->findByUuidOrFail($tagUuid);

        $this->websiteTags->detach($website->id, $tag->id);

        return WebsiteTagsDTO::forWebsite(
            $website->uuid,
            $this->mapTagsToDtos($this->websiteTags->listTagsForWebsite($website->id)),
        );
    }

    /**
     * @throws ModelNotFoundException
     */
    public function sync(string $websiteUuid, SyncWebsiteTagsDTO $payload, User $user): WebsiteTagsDTO
    {
        $website = $this->resolveOwnedWebsite($websiteUuid, $user);

        $tagIds = [];
        foreach ($payload->tag_uuids as $tagUuid) {
            $tagIds[] = $this->tags->findByUuidOrFail($tagUuid)->id;
        }

        $this->websiteTags->sync($website->id, $tagIds);

        return WebsiteTagsDTO::forWebsite(
            $website->uuid,
            $this->mapTagsToDtos($this->websiteTags->listTagsForWebsite($website->id)),
        );
    }

    /**
     * @throws ModelNotFoundException
     */
    private function resolveOwnedWebsite(string $websiteUuid, User $user): Website
    {
        $website = $this->websites->findByUuidForUser($websiteUuid, $user->id);

        if ($website === null) {
            throw (new ModelNotFoundException)->setModel(Website::class);
        }

        return $website;
    }

    /**
     * @param  iterable<int, Tag>  $tags
     * @return list<TagDTO>
     */
    private function mapTagsToDtos(iterable $tags): array
    {
        $dtos = [];

        foreach ($tags as $tag) {
            $dtos[] = TagDTO::fromModel($tag);
        }

        return $dtos;
    }
}

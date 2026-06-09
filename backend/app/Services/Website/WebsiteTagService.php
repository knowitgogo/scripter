<?php

declare(strict_types=1);

namespace App\Services\Website;

use App\DTOs\Tag\TagDTO;
use App\DTOs\Website\SyncWebsiteTagsDTO;
use App\DTOs\Website\WebsiteTagsDTO;
use App\Models\User;
use App\Services\Tag\TagService;

/**
 * Website-scoped facade delegating tag attachment operations to {@see TagService}.
 */
final class WebsiteTagService
{
    public function __construct(
        private readonly TagService $tags,
    ) {}

    /**
     * @return list<TagDTO>
     */
    public function listForWebsite(string $websiteUuid, User $user): array
    {
        return $this->tags->listForWebsite($websiteUuid, $user);
    }

    public function attach(string $websiteUuid, string $tagUuid, User $user): WebsiteTagsDTO
    {
        return $this->tags->attach($websiteUuid, $tagUuid, $user);
    }

    public function detach(string $websiteUuid, string $tagUuid, User $user): WebsiteTagsDTO
    {
        return $this->tags->detach($websiteUuid, $tagUuid, $user);
    }

    public function sync(string $websiteUuid, SyncWebsiteTagsDTO $payload, User $user): WebsiteTagsDTO
    {
        return $this->tags->sync($websiteUuid, $payload, $user);
    }
}

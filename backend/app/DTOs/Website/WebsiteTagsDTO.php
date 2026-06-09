<?php

declare(strict_types=1);

namespace App\DTOs\Website;

use App\DTOs\DataTransferObject;
use App\DTOs\Tag\TagDTO;

/**
 * Tags attached to a website (`WebsiteTagService` response).
 */
final class WebsiteTagsDTO extends DataTransferObject
{
    /**
     * @param  list<TagDTO>  $tags
     */
    public function __construct(
        public readonly string $website_uuid,
        public readonly array $tags,
    ) {}

    /**
     * @param  list<TagDTO>  $tags
     */
    public static function forWebsite(string $websiteUuid, array $tags): self
    {
        return new self(
            website_uuid: $websiteUuid,
            tags: $tags,
        );
    }
}

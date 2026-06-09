<?php

declare(strict_types=1);

namespace App\DTOs\Website;

use App\DTOs\Concerns\MapsFromRequest;
use App\DTOs\DataTransferObject;

/**
 * Query parameters for listing websites owned by the authenticated user.
 */
final class ListWebsitesQueryDTO extends DataTransferObject
{
    use MapsFromRequest;

    /**
     * @param  list<string>  $tag_uuids  When non-empty, only websites with all listed tags are returned.
     */
    public function __construct(
        public readonly array $tag_uuids = [],
    ) {}

    public function hasTagFilter(): bool
    {
        return $this->tag_uuids !== [];
    }
}

<?php

declare(strict_types=1);

namespace App\DTOs\Website;

use App\DTOs\Concerns\MapsFromRequest;
use App\DTOs\DataTransferObject;
use App\Http\Requests\ApiRequest;

/**
 * Payload for replacing a website's tag set (`TagService::sync`).
 */
final class SyncWebsiteTagsDTO extends DataTransferObject
{
    use MapsFromRequest;

    /**
     * @param  list<string>  $tag_uuids
     */
    public function __construct(
        public readonly array $tag_uuids,
    ) {}

    public static function fromRequest(ApiRequest $request): static
    {
        /** @var list<string> $tagUuids */
        $tagUuids = $request->validated('tag_uuids', []);

        return new self(tag_uuids: $tagUuids);
    }
}

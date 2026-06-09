<?php

declare(strict_types=1);

namespace App\DTOs\Tag;

use App\DTOs\Concerns\MapsFromRequest;
use App\DTOs\DataTransferObject;

/**
 * Payload for updating an existing tag.
 */
final class UpdateTagDTO extends DataTransferObject
{
    use MapsFromRequest;

    public function __construct(
        public readonly string $name,
        public readonly string $slug,
    ) {}
}

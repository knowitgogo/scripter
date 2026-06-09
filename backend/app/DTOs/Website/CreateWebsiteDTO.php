<?php

declare(strict_types=1);

namespace App\DTOs\Website;

use App\DTOs\Concerns\MapsFromRequest;
use App\DTOs\DataTransferObject;

/**
 * Payload for creating a new customer website.
 */
final class CreateWebsiteDTO extends DataTransferObject
{
    use MapsFromRequest;

    public function __construct(
        public readonly string $name,
        public readonly string $url,
    ) {}
}

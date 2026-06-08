<?php

declare(strict_types=1);

namespace App\DTOs\OpenApi;

use App\DTOs\DataTransferObject;

/**
 * OpenAPI document metadata and raw specification contents.
 */
final class OpenApiDocumentDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $openapi,
        public readonly string $title,
        public readonly string $version,
        public readonly string $contents,
        public readonly string $format,
    ) {}
}

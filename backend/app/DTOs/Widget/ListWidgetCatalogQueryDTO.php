<?php

declare(strict_types=1);

namespace App\DTOs\Widget;

use App\DTOs\Concerns\MapsFromRequest;
use App\DTOs\DataTransferObject;

/**
 * Query parameters for listing published widgets in the marketplace catalog.
 */
final class ListWidgetCatalogQueryDTO extends DataTransferObject
{
    use MapsFromRequest;

    public function __construct(
        public readonly ?string $search = null,
    ) {}

    public function hasSearch(): bool
    {
        return $this->search !== null && trim($this->search) !== '';
    }

    public function normalizedSearch(): ?string
    {
        if (! $this->hasSearch()) {
            return null;
        }

        return trim($this->search);
    }
}

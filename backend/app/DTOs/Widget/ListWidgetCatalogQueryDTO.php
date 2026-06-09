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

    /**
     * @param  list<string>  $slugs  When non-empty, only widgets with matching slugs are returned.
     */
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?string $category = null,
        public readonly array $slugs = [],
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

    public function hasCategoryFilter(): bool
    {
        return $this->category !== null && trim($this->category) !== '';
    }

    public function normalizedCategory(): ?string
    {
        if (! $this->hasCategoryFilter()) {
            return null;
        }

        return trim($this->category);
    }

    public function hasSlugFilter(): bool
    {
        return $this->slugs !== [];
    }
}

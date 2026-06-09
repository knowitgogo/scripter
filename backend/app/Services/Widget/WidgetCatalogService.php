<?php

declare(strict_types=1);

namespace App\Services\Widget;

use App\DTOs\Widget\ListWidgetCatalogQueryDTO;
use App\DTOs\Widget\WidgetDTO;
use App\Models\Widget;
use App\Repositories\Contracts\WidgetRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Resolves published widgets from the marketplace catalog.
 */
final class WidgetCatalogService
{
    public function __construct(
        private readonly WidgetRepositoryInterface $widgets,
    ) {}

    /**
     * @return list<WidgetDTO>
     */
    public function listPublished(): array
    {
        return $this->widgets->listPublishedOrderedByName(new ListWidgetCatalogQueryDTO())
            ->map(fn (Widget $widget): WidgetDTO => WidgetDTO::fromModel($widget))
            ->values()
            ->all();
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getByUuid(string $uuid): WidgetDTO
    {
        /** @var Widget $widget */
        $widget = $this->widgets->findByUuidOrFail($uuid);

        return WidgetDTO::fromModel($widget);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getBySlug(string $slug): WidgetDTO
    {
        return WidgetDTO::fromModel($this->widgets->findBySlugOrFail($slug));
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Widget;

use App\DTOs\Widget\ListWidgetCatalogQueryDTO;
use App\DTOs\Widget\WidgetDTO;
use App\DTOs\Widget\WidgetVersionDTO;
use App\Models\Widget;
use App\Models\WidgetVersion;
use App\Repositories\Contracts\WidgetRepositoryInterface;
use App\Repositories\Contracts\WidgetVersionRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Marketplace widget catalog operations for widgets and their releases.
 */
final class WidgetService
{
    public function __construct(
        private readonly WidgetRepositoryInterface $widgets,
        private readonly WidgetVersionRepositoryInterface $widgetVersions,
    ) {}

    /**
     * @return list<WidgetDTO>
     */
    public function listPublished(?ListWidgetCatalogQueryDTO $query = null): array
    {
        $query ??= new ListWidgetCatalogQueryDTO;

        return $this->widgets->listPublishedOrderedByName($query->normalizedSearch())
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

    /**
     * @return list<WidgetVersionDTO>
     *
     * @throws ModelNotFoundException
     */
    public function listVersionsForWidget(string $widgetUuid): array
    {
        $widgetId = $this->widgets->findByUuidOrFail($widgetUuid)->id;

        return $this->widgetVersions->listForWidget($widgetId)
            ->map(fn (WidgetVersion $version): WidgetVersionDTO => WidgetVersionDTO::fromModel($version))
            ->values()
            ->all();
    }

    /**
     * @return list<WidgetVersionDTO>
     *
     * @throws ModelNotFoundException
     */
    public function listPublishedVersionsForWidget(string $widgetUuid): array
    {
        $widgetId = $this->widgets->findByUuidOrFail($widgetUuid)->id;

        return $this->widgetVersions->listPublishedForWidget($widgetId)
            ->map(fn (WidgetVersion $version): WidgetVersionDTO => WidgetVersionDTO::fromModel($version))
            ->values()
            ->all();
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getVersionByUuid(string $uuid): WidgetVersionDTO
    {
        /** @var WidgetVersion $widgetVersion */
        $widgetVersion = $this->widgetVersions->findByUuidOrFail($uuid);
        $widgetVersion->load('widget');

        return WidgetVersionDTO::fromModel($widgetVersion);
    }
}

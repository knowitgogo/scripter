<?php

declare(strict_types=1);

namespace App\Services\Widget;

use App\DTOs\Widget\WidgetVersionDTO;
use App\Models\WidgetVersion;
use App\Repositories\Contracts\WidgetRepositoryInterface;
use App\Repositories\Contracts\WidgetVersionRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Resolves widget releases from the marketplace catalog.
 */
final class WidgetVersionService
{
    public function __construct(
        private readonly WidgetRepositoryInterface $widgets,
        private readonly WidgetVersionRepositoryInterface $widgetVersions,
    ) {}

    /**
     * @return list<WidgetVersionDTO>
     */
    public function listForWidget(string $widgetUuid): array
    {
        $widgetId = $this->widgets->findByUuidOrFail($widgetUuid)->id;

        return $this->widgetVersions->listForWidget($widgetId)
            ->map(fn (WidgetVersion $version): WidgetVersionDTO => WidgetVersionDTO::fromModel($version))
            ->values()
            ->all();
    }

    /**
     * @return list<WidgetVersionDTO>
     */
    public function listPublishedForWidget(string $widgetUuid): array
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
    public function getByUuid(string $uuid): WidgetVersionDTO
    {
        /** @var WidgetVersion $widgetVersion */
        $widgetVersion = $this->widgetVersions->findByUuidOrFail($uuid);
        $widgetVersion->load('widget');

        return WidgetVersionDTO::fromModel($widgetVersion);
    }
}

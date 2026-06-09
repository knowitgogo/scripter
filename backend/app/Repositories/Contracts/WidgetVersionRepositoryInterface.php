<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Enums\WidgetVersionStatus;
use App\Models\WidgetVersion;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository contract for {@see \App\Models\WidgetVersion} aggregates.
 */
interface WidgetVersionRepositoryInterface extends UuidRepositoryInterface
{
    public function findByWidgetAndVersion(int $widgetId, string $version): ?WidgetVersion;

    public function findPublishedForWidget(int $widgetId): ?WidgetVersion;

    /**
     * @return Collection<int, WidgetVersion>
     */
    public function listForWidget(int $widgetId): Collection;

    /**
     * @return Collection<int, WidgetVersion>
     */
    public function listPublishedForWidget(int $widgetId): Collection;

    /**
     * @return Collection<int, WidgetVersion>
     */
    public function listByStatus(WidgetVersionStatus $status): Collection;
}

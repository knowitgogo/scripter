<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\WidgetVersionStatus;
use App\Models\WidgetVersion;
use App\Repositories\Contracts\WidgetVersionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Eloquent persistence for widget version aggregates.
 */
final class EloquentWidgetVersionRepository extends UuidEloquentRepository implements WidgetVersionRepositoryInterface
{
    public function findByWidgetAndVersion(int $widgetId, string $version): ?WidgetVersion
    {
        /** @var WidgetVersion|null $widgetVersion */
        $widgetVersion = $this->newModelQuery()
            ->where('widget_id', $widgetId)
            ->where('version', $version)
            ->first();

        return $widgetVersion;
    }

    public function findPublishedForWidget(int $widgetId): ?WidgetVersion
    {
        /** @var WidgetVersion|null $widgetVersion */
        $widgetVersion = $this->newModelQuery()
            ->where('widget_id', $widgetId)
            ->where('status', WidgetVersionStatus::Published->value)
            ->orderByDesc('created_at')
            ->first();

        return $widgetVersion;
    }

    /**
     * @return Collection<int, WidgetVersion>
     */
    public function listForWidget(int $widgetId): Collection
    {
        return $this->newModelQuery()
            ->with('widget')
            ->where('widget_id', $widgetId)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * @return Collection<int, WidgetVersion>
     */
    public function listPublishedForWidget(int $widgetId): Collection
    {
        return $this->newModelQuery()
            ->with('widget')
            ->where('widget_id', $widgetId)
            ->where('status', WidgetVersionStatus::Published->value)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * @return Collection<int, WidgetVersion>
     */
    public function listByStatus(WidgetVersionStatus $status): Collection
    {
        return $this->newModelQuery()
            ->with('widget')
            ->where('status', $status->value)
            ->orderByDesc('created_at')
            ->get();
    }

    protected function model(): string
    {
        return WidgetVersion::class;
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\WidgetTemplate;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository contract for {@see \App\Models\WidgetTemplate} aggregates.
 */
interface WidgetTemplateRepositoryInterface extends UuidRepositoryInterface
{
    public function findByWidgetAndSlug(int $widgetId, string $slug): ?WidgetTemplate;

    public function findByUuidForWidget(int $widgetId, string $uuid): ?WidgetTemplate;

    public function findByUuidForWidgetOrFail(int $widgetId, string $uuid): WidgetTemplate;

    public function findDefaultForWidget(int $widgetId): ?WidgetTemplate;

    public function slugExistsForWidget(int $widgetId, string $slug, ?string $excludeTemplateUuid = null): bool;

    public function clearDefaultForWidget(int $widgetId): void;

    /**
     * @return Collection<int, WidgetTemplate>
     */
    public function listForWidget(int $widgetId): Collection;
}

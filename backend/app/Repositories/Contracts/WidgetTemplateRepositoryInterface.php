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

    public function findDefaultForWidget(int $widgetId): ?WidgetTemplate;

    /**
     * @return Collection<int, WidgetTemplate>
     */
    public function listForWidget(int $widgetId): Collection;
}

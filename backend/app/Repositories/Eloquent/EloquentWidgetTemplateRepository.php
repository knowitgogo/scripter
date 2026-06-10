<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\WidgetTemplate;
use App\Repositories\Contracts\WidgetTemplateRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Eloquent persistence for widget template aggregates.
 */
final class EloquentWidgetTemplateRepository extends UuidEloquentRepository implements WidgetTemplateRepositoryInterface
{
    public function findByWidgetAndSlug(int $widgetId, string $slug): ?WidgetTemplate
    {
        /** @var WidgetTemplate|null $template */
        $template = $this->newModelQuery()
            ->with('widget')
            ->where('widget_id', $widgetId)
            ->where('slug', $slug)
            ->first();

        return $template;
    }

    public function findDefaultForWidget(int $widgetId): ?WidgetTemplate
    {
        /** @var WidgetTemplate|null $template */
        $template = $this->newModelQuery()
            ->with('widget')
            ->where('widget_id', $widgetId)
            ->where('is_default', true)
            ->first();

        return $template;
    }

    /**
     * @return Collection<int, WidgetTemplate>
     */
    public function listForWidget(int $widgetId): Collection
    {
        return $this->newModelQuery()
            ->with('widget')
            ->where('widget_id', $widgetId)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
    }

    protected function model(): string
    {
        return WidgetTemplate::class;
    }
}

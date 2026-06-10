<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\WidgetTemplate;
use App\Repositories\Contracts\WidgetTemplateRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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

    public function findByUuidForWidget(int $widgetId, string $uuid): ?WidgetTemplate
    {
        /** @var WidgetTemplate|null $template */
        $template = $this->newModelQuery()
            ->with('widget')
            ->where('widget_id', $widgetId)
            ->where('uuid', $uuid)
            ->first();

        return $template;
    }

    public function findByUuidForWidgetOrFail(int $widgetId, string $uuid): WidgetTemplate
    {
        $template = $this->findByUuidForWidget($widgetId, $uuid);

        if ($template === null) {
            throw (new ModelNotFoundException)->setModel($this->model());
        }

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

    public function slugExistsForWidget(int $widgetId, string $slug, ?string $excludeTemplateUuid = null): bool
    {
        $query = $this->newModelQuery()
            ->where('widget_id', $widgetId)
            ->where('slug', $slug);

        if ($excludeTemplateUuid !== null) {
            $query->where('uuid', '!=', $excludeTemplateUuid);
        }

        return $query->exists();
    }

    public function clearDefaultForWidget(int $widgetId): void
    {
        $this->newModelQuery()
            ->where('widget_id', $widgetId)
            ->where('is_default', true)
            ->update(['is_default' => false]);
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

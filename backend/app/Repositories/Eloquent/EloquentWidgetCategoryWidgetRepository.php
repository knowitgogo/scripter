<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Widget;
use App\Models\WidgetCategory;
use App\Repositories\Contracts\WidgetCategoryWidgetRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Eloquent persistence for widget–category pivot rows.
 */
final class EloquentWidgetCategoryWidgetRepository implements WidgetCategoryWidgetRepositoryInterface
{
    public function attach(int $widgetId, int $categoryId): void
    {
        $widget = $this->findWidgetOrFail($widgetId);

        $widget->categories()->syncWithoutDetaching([$categoryId]);
    }

    public function detach(int $widgetId, int $categoryId): void
    {
        $widget = $this->findWidgetOrFail($widgetId);

        $widget->categories()->detach($categoryId);
    }

    /**
     * @param  list<int>  $categoryIds
     * @return array{attached: list<int>, detached: list<int>, updated: list<int>}
     */
    public function sync(int $widgetId, array $categoryIds): array
    {
        $widget = $this->findWidgetOrFail($widgetId);

        /** @var array{attached: list<int>, detached: list<int>, updated: list<int>} $result */
        $result = $widget->categories()->sync($categoryIds);

        return $result;
    }

    /**
     * @return Collection<int, WidgetCategory>
     */
    public function listCategoriesForWidget(int $widgetId): Collection
    {
        $widget = $this->findWidgetOrFail($widgetId);

        return $widget->categories()->orderBy('name')->get();
    }

    public function isAttached(int $widgetId, int $categoryId): bool
    {
        $widget = $this->findWidgetOrFail($widgetId);

        return $widget->categories()->where('widget_categories.id', $categoryId)->exists();
    }

    private function findWidgetOrFail(int $widgetId): Widget
    {
        /** @var Widget|null $widget */
        $widget = Widget::query()->find($widgetId);

        if ($widget === null) {
            throw (new ModelNotFoundException)->setModel(Widget::class);
        }

        return $widget;
    }
}

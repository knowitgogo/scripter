<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTOs\Widget\ListWidgetCatalogQueryDTO;
use App\Enums\WidgetStatus;
use App\Models\Widget;
use App\Repositories\Contracts\WidgetRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Eloquent persistence for widget catalog aggregates.
 */
final class EloquentWidgetRepository extends UuidEloquentRepository implements WidgetRepositoryInterface
{
    public function findBySlug(string $slug): ?Widget
    {
        /** @var Widget|null $widget */
        $widget = $this->newModelQuery()->where('slug', $slug)->first();

        return $widget;
    }

    public function findBySlugOrFail(string $slug): Widget
    {
        $widget = $this->findBySlug($slug);

        if ($widget === null) {
            throw (new ModelNotFoundException)->setModel($this->model());
        }

        return $widget;
    }

    /**
     * @return Collection<int, Widget>
     */
    public function listPublishedOrderedByName(?ListWidgetCatalogQueryDTO $query = null): Collection
    {
        return $this->listByStatus(WidgetStatus::Published, $query);
    }

    /**
     * @return Collection<int, Widget>
     */
    public function listByStatus(WidgetStatus $status, ?ListWidgetCatalogQueryDTO $query = null): Collection
    {
        $query ??= new ListWidgetCatalogQueryDTO;

        $builder = $this->newModelQuery()
            ->where('status', $status->value);

        $this->applyCatalogFilters($builder, $query);

        return $builder->orderBy('name')->get();
    }

    protected function model(): string
    {
        return Widget::class;
    }

    private function applyCatalogFilters(Builder $builder, ListWidgetCatalogQueryDTO $query): void
    {
        if ($query->hasSearch()) {
            $term = '%'.$query->normalizedSearch().'%';
            $builder->where(function (Builder $searchQuery) use ($term): void {
                $searchQuery->where('name', 'like', $term)
                    ->orWhere('slug', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        if ($query->hasSlugFilter()) {
            $builder->whereIn('slug', $query->slugs);
        }

        if ($query->hasCategoryFilter()) {
            $category = $query->normalizedCategory();
            $builder->where(function (Builder $categoryQuery) use ($category): void {
                $categoryQuery->where('slug', $category)
                    ->orWhere('slug', 'like', $category.'-%');
            });
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\WidgetCategory;
use App\Repositories\Contracts\WidgetCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Eloquent persistence for widget category aggregates.
 */
final class EloquentWidgetCategoryRepository extends UuidEloquentRepository implements WidgetCategoryRepositoryInterface
{
    public function findBySlug(string $slug): ?WidgetCategory
    {
        /** @var WidgetCategory|null $category */
        $category = $this->newModelQuery()->where('slug', $slug)->first();

        return $category;
    }

    public function findBySlugOrFail(string $slug): WidgetCategory
    {
        $category = $this->findBySlug($slug);

        if ($category === null) {
            throw (new ModelNotFoundException)->setModel($this->model());
        }

        return $category;
    }

    /**
     * @return Collection<int, WidgetCategory>
     */
    public function listOrderedByName(): Collection
    {
        return $this->newModelQuery()->orderBy('name')->get();
    }

    protected function model(): string
    {
        return WidgetCategory::class;
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\WidgetStatus;
use App\Models\Widget;
use App\Repositories\Contracts\WidgetRepositoryInterface;
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
    public function listPublishedOrderedByName(?string $search = null): Collection
    {
        return $this->listByStatus(WidgetStatus::Published, $search);
    }

    /**
     * @return Collection<int, Widget>
     */
    public function listByStatus(WidgetStatus $status, ?string $search = null): Collection
    {
        $query = $this->newModelQuery()
            ->where('status', $status->value);

        if ($search !== null && $search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%');
            });
        }

        return $query->orderBy('name')->get();
    }

    protected function model(): string
    {
        return Widget::class;
    }
}

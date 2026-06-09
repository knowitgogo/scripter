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
    public function listPublishedOrderedByName(): Collection
    {
        return $this->listByStatus(WidgetStatus::Published);
    }

    /**
     * @return Collection<int, Widget>
     */
    public function listByStatus(WidgetStatus $status): Collection
    {
        return $this->newModelQuery()
            ->where('status', $status->value)
            ->orderBy('name')
            ->get();
    }

    protected function model(): string
    {
        return Widget::class;
    }
}

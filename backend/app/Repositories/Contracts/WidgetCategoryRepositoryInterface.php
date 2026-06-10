<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\WidgetCategory;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository contract for {@see \App\Models\WidgetCategory} aggregates.
 */
interface WidgetCategoryRepositoryInterface extends UuidRepositoryInterface
{
    public function findBySlug(string $slug): ?WidgetCategory;

    public function findBySlugOrFail(string $slug): WidgetCategory;

    /**
     * @return Collection<int, WidgetCategory>
     */
    public function listOrderedByName(): Collection;
}

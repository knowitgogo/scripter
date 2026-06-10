<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\WidgetCategory;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository contract for widget–category pivot persistence.
 */
interface WidgetCategoryWidgetRepositoryInterface
{
    public function attach(int $widgetId, int $categoryId): void;

    public function detach(int $widgetId, int $categoryId): void;

    /**
     * @param  list<int>  $categoryIds
     * @return array{attached: list<int>, detached: list<int>, updated: list<int>}
     */
    public function sync(int $widgetId, array $categoryIds): array;

    /**
     * @return Collection<int, WidgetCategory>
     */
    public function listCategoriesForWidget(int $widgetId): Collection;

    public function isAttached(int $widgetId, int $categoryId): bool;
}

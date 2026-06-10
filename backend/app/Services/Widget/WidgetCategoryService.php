<?php

declare(strict_types=1);

namespace App\Services\Widget;

use App\DTOs\Widget\WidgetCategoryDTO;
use App\Models\WidgetCategory;
use App\Repositories\Contracts\WidgetCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Resolves marketplace widget categories for catalog discovery.
 */
final class WidgetCategoryService
{
    public function __construct(
        private readonly WidgetCategoryRepositoryInterface $categories,
    ) {}

    /**
     * @return list<WidgetCategoryDTO>
     */
    public function list(): array
    {
        return $this->categories->listOrderedByName()
            ->map(fn (WidgetCategory $category): WidgetCategoryDTO => WidgetCategoryDTO::fromModel($category))
            ->values()
            ->all();
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getByUuid(string $uuid): WidgetCategoryDTO
    {
        /** @var WidgetCategory $category */
        $category = $this->categories->findByUuidOrFail($uuid);

        return WidgetCategoryDTO::fromModel($category);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getBySlug(string $slug): WidgetCategoryDTO
    {
        return WidgetCategoryDTO::fromModel($this->categories->findBySlugOrFail($slug));
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Widget;

use App\DTOs\Widget\SyncWidgetCategoriesDTO;
use App\DTOs\Widget\WidgetCategoriesDTO;
use App\DTOs\Widget\WidgetCategoryDTO;
use App\Models\WidgetCategory;
use App\Repositories\Contracts\WidgetCategoryRepositoryInterface;
use App\Repositories\Contracts\WidgetCategoryWidgetRepositoryInterface;
use App\Repositories\Contracts\WidgetRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Resolves marketplace widget categories and their widget assignments.
 */
final class WidgetCategoryService
{
    public function __construct(
        private readonly WidgetCategoryRepositoryInterface $categories,
        private readonly WidgetCategoryWidgetRepositoryInterface $widgetCategories,
        private readonly WidgetRepositoryInterface $widgets,
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

    /**
     * @return list<WidgetCategoryDTO>
     *
     * @throws ModelNotFoundException
     */
    public function listForWidget(string $widgetUuid): array
    {
        $widget = $this->widgets->findByUuidOrFail($widgetUuid);

        return $this->mapCategoriesToDtos(
            $this->widgetCategories->listCategoriesForWidget($widget->id),
        );
    }

    /**
     * @throws ModelNotFoundException
     */
    public function attach(string $widgetUuid, string $categoryUuid): WidgetCategoriesDTO
    {
        $widget = $this->widgets->findByUuidOrFail($widgetUuid);
        $category = $this->categories->findByUuidOrFail($categoryUuid);

        $this->widgetCategories->attach($widget->id, $category->id);

        return WidgetCategoriesDTO::forWidget(
            $widget->uuid,
            $this->mapCategoriesToDtos($this->widgetCategories->listCategoriesForWidget($widget->id)),
        );
    }

    /**
     * @throws ModelNotFoundException
     */
    public function detach(string $widgetUuid, string $categoryUuid): WidgetCategoriesDTO
    {
        $widget = $this->widgets->findByUuidOrFail($widgetUuid);
        $category = $this->categories->findByUuidOrFail($categoryUuid);

        $this->widgetCategories->detach($widget->id, $category->id);

        return WidgetCategoriesDTO::forWidget(
            $widget->uuid,
            $this->mapCategoriesToDtos($this->widgetCategories->listCategoriesForWidget($widget->id)),
        );
    }

    /**
     * @throws ModelNotFoundException
     */
    public function sync(string $widgetUuid, SyncWidgetCategoriesDTO $payload): WidgetCategoriesDTO
    {
        $widget = $this->widgets->findByUuidOrFail($widgetUuid);

        $categoryIds = [];
        foreach ($payload->category_uuids as $categoryUuid) {
            $categoryIds[] = $this->categories->findByUuidOrFail($categoryUuid)->id;
        }

        $this->widgetCategories->sync($widget->id, $categoryIds);

        return WidgetCategoriesDTO::forWidget(
            $widget->uuid,
            $this->mapCategoriesToDtos($this->widgetCategories->listCategoriesForWidget($widget->id)),
        );
    }

    /**
     * @param  iterable<int, WidgetCategory>  $categories
     * @return list<WidgetCategoryDTO>
     */
    private function mapCategoriesToDtos(iterable $categories): array
    {
        $dtos = [];

        foreach ($categories as $category) {
            $dtos[] = WidgetCategoryDTO::fromModel($category);
        }

        return $dtos;
    }
}

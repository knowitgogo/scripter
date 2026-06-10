<?php

declare(strict_types=1);

namespace App\DTOs\Widget;

use App\DTOs\DataTransferObject;

/**
 * Categories attached to a widget (`WidgetCategoryService` attach/detach/sync response).
 */
final class WidgetCategoriesDTO extends DataTransferObject
{
    /**
     * @param  list<WidgetCategoryDTO>  $categories
     */
    public function __construct(
        public readonly string $widget_uuid,
        public readonly array $categories,
    ) {}

    /**
     * @param  list<WidgetCategoryDTO>  $categories
     */
    public static function forWidget(string $widgetUuid, array $categories): self
    {
        return new self(
            widget_uuid: $widgetUuid,
            categories: $categories,
        );
    }
}

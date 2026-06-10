<?php

declare(strict_types=1);

namespace App\DTOs\Widget;

use App\DTOs\DataTransferObject;

/**
 * Templates assigned to a widget (`WidgetTemplateAssignmentService` assign/unassign response).
 */
final class WidgetTemplatesDTO extends DataTransferObject
{
    /**
     * @param  list<WidgetTemplateDTO>  $templates
     */
    public function __construct(
        public readonly string $widget_uuid,
        public readonly array $templates,
    ) {}

    /**
     * @param  list<WidgetTemplateDTO>  $templates
     */
    public static function forWidget(string $widgetUuid, array $templates): self
    {
        return new self(
            widget_uuid: $widgetUuid,
            templates: $templates,
        );
    }
}

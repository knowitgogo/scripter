<?php

declare(strict_types=1);

namespace App\DTOs\Widget;

use App\DTOs\DataTransferObject;
use App\Models\WidgetTemplate;
use Illuminate\Database\Eloquent\Model;

/**
 * Public widget template representation returned by Widget domain services.
 */
final class WidgetTemplateDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $widget_uuid,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $content,
        public readonly bool $is_default = false,
        public readonly ?string $description = null,
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null,
    ) {}

    public static function fromModel(Model $model): static
    {
        if (! $model instanceof WidgetTemplate) {
            throw new \InvalidArgumentException('Expected instance of '.WidgetTemplate::class);
        }

        if (! $model->relationLoaded('widget')) {
            $model->load('widget');
        }

        return new self(
            uuid: $model->uuid,
            widget_uuid: $model->widget->uuid,
            name: $model->name,
            slug: $model->slug,
            content: $model->content,
            is_default: $model->is_default,
            description: $model->description,
            created_at: $model->created_at?->format(\DateTimeInterface::ATOM),
            updated_at: $model->updated_at?->format(\DateTimeInterface::ATOM),
        );
    }
}

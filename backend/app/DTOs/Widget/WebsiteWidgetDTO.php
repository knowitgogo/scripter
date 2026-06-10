<?php

declare(strict_types=1);

namespace App\DTOs\Widget;

use App\DTOs\DataTransferObject;
use App\Enums\WebsiteWidgetStatus;
use App\Models\WebsiteWidget;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;

/**
 * Public website widget installation returned by Widget domain services.
 */
final class WebsiteWidgetDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $website_uuid,
        public readonly string $widget_version_uuid,
        public readonly WebsiteWidgetStatus $status,
        public readonly ?array $configuration = null,
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null,
    ) {}

    public static function fromModel(Model $model): static
    {
        if (! $model instanceof WebsiteWidget) {
            throw new \InvalidArgumentException('Expected instance of '.WebsiteWidget::class);
        }

        if (! $model->relationLoaded('website')) {
            $model->load('website');
        }

        if (! $model->relationLoaded('widgetVersion')) {
            $model->load('widgetVersion');
        }

        return new self(
            uuid: $model->uuid,
            website_uuid: $model->website->uuid,
            widget_version_uuid: $model->widgetVersion->uuid,
            status: $model->status,
            configuration: $model->configuration_json,
            created_at: $model->created_at?->format(\DateTimeInterface::ATOM),
            updated_at: $model->updated_at?->format(\DateTimeInterface::ATOM),
        );
    }

    protected function transformValue(mixed $value): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        return parent::transformValue($value);
    }
}

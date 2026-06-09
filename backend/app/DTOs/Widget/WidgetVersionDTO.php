<?php

declare(strict_types=1);

namespace App\DTOs\Widget;

use App\DTOs\DataTransferObject;
use App\Enums\WidgetVersionStatus;
use App\Models\WidgetVersion;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;

/**
 * Public widget version representation returned by Widget domain services.
 */
final class WidgetVersionDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $widget_uuid,
        public readonly string $version,
        public readonly WidgetVersionStatus $status,
        public readonly ?string $asset_manifest_url = null,
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null,
    ) {}

    public static function fromModel(Model $model): static
    {
        if (! $model instanceof WidgetVersion) {
            throw new \InvalidArgumentException('Expected instance of '.WidgetVersion::class);
        }

        if (! $model->relationLoaded('widget')) {
            $model->load('widget');
        }

        return new self(
            uuid: $model->uuid,
            widget_uuid: $model->widget->uuid,
            version: $model->version,
            status: $model->status,
            asset_manifest_url: $model->asset_manifest_url,
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

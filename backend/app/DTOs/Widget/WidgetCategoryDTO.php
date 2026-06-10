<?php

declare(strict_types=1);

namespace App\DTOs\Widget;

use App\DTOs\DataTransferObject;
use App\Models\WidgetCategory;
use Illuminate\Database\Eloquent\Model;

/**
 * Public widget category representation returned by Widget domain services.
 */
final class WidgetCategoryDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description = null,
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null,
    ) {}

    public static function fromModel(Model $model): static
    {
        if (! $model instanceof WidgetCategory) {
            throw new \InvalidArgumentException('Expected instance of '.WidgetCategory::class);
        }

        return new self(
            uuid: $model->uuid,
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            created_at: $model->created_at?->format(\DateTimeInterface::ATOM),
            updated_at: $model->updated_at?->format(\DateTimeInterface::ATOM),
        );
    }
}

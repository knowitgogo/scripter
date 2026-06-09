<?php

declare(strict_types=1);

namespace App\DTOs\Widget;

use App\DTOs\DataTransferObject;
use App\Enums\WidgetStatus;
use App\Models\Widget;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;

/**
 * Public widget catalog representation returned by Widget domain services.
 */
final class WidgetDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $name,
        public readonly string $slug,
        public readonly WidgetStatus $status,
        public readonly ?string $description = null,
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null,
    ) {}

    public static function fromModel(Model $model): static
    {
        if (! $model instanceof Widget) {
            throw new \InvalidArgumentException('Expected instance of '.Widget::class);
        }

        return new self(
            uuid: $model->uuid,
            name: $model->name,
            slug: $model->slug,
            status: $model->status,
            description: $model->description,
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

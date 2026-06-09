<?php

declare(strict_types=1);

namespace App\DTOs\Tag;

use App\DTOs\DataTransferObject;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;

/**
 * Public tag representation returned by Tag domain services.
 */
final class TagDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null,
    ) {}

    public static function fromModel(Model $model): static
    {
        if (! $model instanceof Tag) {
            throw new \InvalidArgumentException('Expected instance of '.Tag::class);
        }

        return new self(
            uuid: $model->uuid,
            name: $model->name,
            slug: $model->slug,
            created_at: $model->created_at?->format(\DateTimeInterface::ATOM),
            updated_at: $model->updated_at?->format(\DateTimeInterface::ATOM),
        );
    }
}

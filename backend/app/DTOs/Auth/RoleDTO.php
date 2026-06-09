<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\DTOs\DataTransferObject;
use App\Enums\RoleSlug;
use App\Models\Role;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;

/**
 * Public role representation for API and cross-layer communication.
 */
final class RoleDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $name,
        public readonly RoleSlug $slug,
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null,
    ) {}

    public static function fromModel(Model $model): static
    {
        if (! $model instanceof Role) {
            throw new \InvalidArgumentException('Expected instance of '.Role::class);
        }

        return new self(
            uuid: $model->uuid,
            name: $model->name,
            slug: $model->slug,
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

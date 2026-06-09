<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\DTOs\DataTransferObject;
use App\Enums\UserStatus;
use App\Models\User;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;

/**
 * Public user representation returned by Auth domain services.
 */
final class UserDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $name,
        public readonly string $email,
        public readonly UserStatus $status,
        public readonly RoleDTO $role,
        public readonly ?string $last_login_at = null,
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null,
    ) {}

    public static function fromModel(Model $model): static
    {
        if (! $model instanceof User) {
            throw new \InvalidArgumentException('Expected instance of '.User::class);
        }

        $model->loadMissing('role');

        return new self(
            uuid: $model->uuid,
            name: $model->name,
            email: $model->email,
            status: $model->status,
            role: RoleDTO::fromModel($model->role),
            last_login_at: $model->last_login_at?->format(\DateTimeInterface::ATOM),
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

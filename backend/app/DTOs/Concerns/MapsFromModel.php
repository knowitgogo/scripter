<?php

declare(strict_types=1);

namespace App\DTOs\Concerns;

use App\DTOs\DataTransferObject;
use Illuminate\Database\Eloquent\Model;

/**
 * Maps an Eloquent model to an immutable DTO.
 *
 * @mixin DataTransferObject
 */
trait MapsFromModel
{
    /**
     * @return list<string>
     */
    protected static function hiddenModelAttributes(): array
    {
        return ['id'];
    }

    public static function fromModel(Model $model): static
    {
        $attributes = $model->toArray();

        foreach (static::hiddenModelAttributes() as $hidden) {
            unset($attributes[$hidden]);
        }

        return static::fromArray($attributes);
    }
}

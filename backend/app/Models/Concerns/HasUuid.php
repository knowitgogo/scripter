<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Support\UuidGenerator;
use Illuminate\Database\Eloquent\Model;

/**
 * Assigns a UUID on model creation for public-facing identifiers.
 *
 * @mixin Model
 */
trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function (Model $model): void {
            if (empty($model->getAttribute('uuid'))) {
                $model->setAttribute('uuid', UuidGenerator::generate());
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}

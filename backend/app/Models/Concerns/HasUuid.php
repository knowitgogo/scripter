<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Support\UuidGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Assigns a UUID on model creation and configures route-model binding.
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

        static::updating(function (Model $model): void {
            if ($model->isDirty('uuid')) {
                $model->setAttribute('uuid', $model->getOriginal('uuid'));
            }
        });
    }

    public function initializeHasUuid(): void
    {
        $this->mergeCasts([
            'uuid' => 'string',
        ]);
    }

    public function getRouteKeyName(): string
    {
        return config('uuids.column', 'uuid');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWhereUuid(Builder $query, string $uuid): Builder
    {
        return $query->where($this->qualifyColumn($this->getRouteKeyName()), $uuid);
    }
}

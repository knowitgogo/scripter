<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WidgetStatus;
use Database\Factories\WidgetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Marketplace catalog widget discoverable by customers.
 */
#[Fillable(['name', 'slug', 'description', 'status'])]
final class Widget extends PublicEntity
{
    /** @use HasFactory<WidgetFactory> */
    use HasFactory;

    /**
     * @return HasMany<WidgetVersion, $this>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(WidgetVersion::class);
    }

    /**
     * @return BelongsToMany<WidgetCategory, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(WidgetCategory::class, 'widget_category_widget')
            ->using(WidgetCategoryWidget::class)
            ->withTimestamps();
    }

    /**
     * @return HasMany<WidgetTemplate, $this>
     */
    public function templates(): HasMany
    {
        return $this->hasMany(WidgetTemplate::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => WidgetStatus::class,
        ];
    }

    protected static function newFactory(): WidgetFactory
    {
        return WidgetFactory::new();
    }
}

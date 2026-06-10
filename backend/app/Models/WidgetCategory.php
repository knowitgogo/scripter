<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WidgetCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Marketplace taxonomy grouping widgets for discovery and filtering.
 */
#[Fillable(['name', 'slug', 'description'])]
final class WidgetCategory extends PublicEntity
{
    /** @use HasFactory<WidgetCategoryFactory> */
    use HasFactory;

    /**
     * @return BelongsToMany<Widget, $this>
     */
    public function widgets(): BelongsToMany
    {
        return $this->belongsToMany(Widget::class, 'widget_category_widget')
            ->using(WidgetCategoryWidget::class)
            ->withTimestamps();
    }

    protected static function newFactory(): WidgetCategoryFactory
    {
        return WidgetCategoryFactory::new();
    }
}

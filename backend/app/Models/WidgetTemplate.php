<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WidgetTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Embed or hosted install snippet template for a marketplace widget.
 */
#[Fillable(['widget_id', 'name', 'slug', 'description', 'content', 'is_default'])]
#[Hidden(['widget_id'])]
final class WidgetTemplate extends PublicEntity
{
    /** @use HasFactory<WidgetTemplateFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Widget, $this>
     */
    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    protected static function newFactory(): WidgetTemplateFactory
    {
        return WidgetTemplateFactory::new();
    }
}

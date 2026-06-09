<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WidgetVersionStatus;
use Database\Factories\WidgetVersionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Semver release of a marketplace widget with asset manifest metadata.
 */
#[Fillable(['widget_id', 'version', 'status', 'asset_manifest_url'])]
#[Hidden(['widget_id'])]
final class WidgetVersion extends PublicEntity
{
    /** @use HasFactory<WidgetVersionFactory> */
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
            'status' => WidgetVersionStatus::class,
        ];
    }

    protected static function newFactory(): WidgetVersionFactory
    {
        return WidgetVersionFactory::new();
    }
}

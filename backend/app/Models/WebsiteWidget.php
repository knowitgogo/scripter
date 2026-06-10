<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WebsiteWidgetStatus;
use Database\Factories\WebsiteWidgetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Widget version installed on a customer-owned website.
 */
#[Fillable(['website_id', 'widget_version_id', 'status', 'configuration_json'])]
#[Hidden(['website_id', 'widget_version_id'])]
final class WebsiteWidget extends PublicEntity
{
    /** @use HasFactory<WebsiteWidgetFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Website, $this>
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * @return BelongsTo<WidgetVersion, $this>
     */
    public function widgetVersion(): BelongsTo
    {
        return $this->belongsTo(WidgetVersion::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => WebsiteWidgetStatus::class,
            'configuration_json' => 'array',
        ];
    }

    protected static function newFactory(): WebsiteWidgetFactory
    {
        return WebsiteWidgetFactory::new();
    }
}

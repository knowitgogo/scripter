<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Reusable label that can be attached to multiple websites.
 */
#[Fillable(['name', 'slug'])]
final class Tag extends PublicEntity
{
    /** @use HasFactory<TagFactory> */
    use HasFactory;

    /**
     * @return BelongsToMany<Website, $this>
     */
    public function websites(): BelongsToMany
    {
        return $this->belongsToMany(Website::class, 'website_tags')
            ->using(WebsiteTag::class)
            ->withTimestamps();
    }

    protected static function newFactory(): TagFactory
    {
        return TagFactory::new();
    }
}

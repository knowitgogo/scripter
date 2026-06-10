<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WebsiteStatus;
use Database\Factories\WebsiteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Customer-owned website where widgets are installed.
 */
#[Fillable(['user_id', 'name', 'url', 'status'])]
#[Hidden(['user_id'])]
final class Website extends PublicEntity
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'website_tags')
            ->using(WebsiteTag::class)
            ->withTimestamps();
    }

    /**
     * @return HasMany<WebsiteWidget, $this>
     */
    public function websiteWidgets(): HasMany
    {
        return $this->hasMany(WebsiteWidget::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => WebsiteStatus::class,
        ];
    }

    protected static function newFactory(): WebsiteFactory
    {
        return WebsiteFactory::new();
    }
}

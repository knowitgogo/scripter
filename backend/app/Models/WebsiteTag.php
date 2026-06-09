<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot row linking a website to a reusable tag.
 */
final class WebsiteTag extends Pivot
{
    protected $table = 'website_tags';
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot row linking a widget to a marketplace category.
 */
final class WidgetCategoryWidget extends Pivot
{
    protected $table = 'widget_category_widget';
}

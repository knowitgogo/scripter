<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Marketplace widget catalog lifecycle status.
 */
enum WidgetStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Deprecated = 'deprecated';
}

<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Widget release lifecycle status.
 */
enum WidgetVersionStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Deprecated = 'deprecated';
}

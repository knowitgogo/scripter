<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Widget installation lifecycle status on a customer website.
 */
enum WebsiteWidgetStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';
}

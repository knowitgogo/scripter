<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Website lifecycle status.
 */
enum WebsiteStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';
}

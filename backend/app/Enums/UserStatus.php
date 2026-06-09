<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * User account status.
 */
enum UserStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Pending = 'pending';
}

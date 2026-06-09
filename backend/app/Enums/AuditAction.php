<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Canonical audit action identifiers.
 */
enum AuditAction: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case Restored = 'restored';
    case Authenticated = 'authenticated';
    case LoggedOut = 'logged_out';
    case Authorized = 'authorized';
    case Suspended = 'suspended';
    case Impersonated = 'impersonated';
    case Published = 'published';
    case Deprecated = 'deprecated';
}

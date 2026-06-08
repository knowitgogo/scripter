<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Centralized UUID generation for public identifiers.
 */
final class UuidGenerator
{
    public static function generate(): string
    {
        return (string) Str::uuid();
    }

    public static function isValid(string $uuid): bool
    {
        return Str::isUuid($uuid);
    }
}

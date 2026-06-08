<?php

declare(strict_types=1);

namespace App\Support\Database;

use Illuminate\Database\Schema\Blueprint;

/**
 * Migration helpers for public UUID columns.
 */
final class BlueprintMacros
{
    public static function register(): void
    {
        Blueprint::macro('publicUuid', function (string $column = 'uuid'): Blueprint {
            /** @var Blueprint $this */
            $this->uuid($column)->unique();

            return $this;
        });
    }
}

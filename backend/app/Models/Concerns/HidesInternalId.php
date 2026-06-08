<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Ensures internal integer primary keys are never serialized to API consumers.
 *
 * @mixin Model
 */
trait HidesInternalId
{
    public function initializeHidesInternalId(): void
    {
        $this->hidden = array_values(array_unique(array_merge(
            $this->hidden ?? [],
            ['id'],
        )));
    }
}

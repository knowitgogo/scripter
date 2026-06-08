<?php

declare(strict_types=1);

namespace App\DTOs;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * Base immutable data carrier for cross-layer communication.
 *
 * Domain DTOs extend this class and implement fromRequest/fromModel factories.
 */
abstract class DataTransferObject implements Arrayable, JsonSerializable
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

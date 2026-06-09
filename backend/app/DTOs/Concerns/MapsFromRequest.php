<?php

declare(strict_types=1);

namespace App\DTOs\Concerns;

use App\DTOs\DataTransferObject;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Maps validated Form Request input to an immutable DTO.
 *
 * @mixin DataTransferObject
 */
trait MapsFromRequest
{
    public static function fromRequest(FormRequest $request): static
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        return static::fromArray($validated);
    }
}

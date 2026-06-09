<?php

declare(strict_types=1);

namespace App\DTOs\Widget;

use App\DTOs\Concerns\MapsFromRequest;
use App\DTOs\DataTransferObject;
use App\Enums\WidgetStatus;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Payload for registering a widget in the marketplace catalog.
 */
final class RegisterWidgetDTO extends DataTransferObject
{
    use MapsFromRequest;

    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description = null,
        public readonly ?WidgetStatus $status = null,
    ) {}

    public static function fromRequest(FormRequest $request): static
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        if (isset($validated['status']) && is_string($validated['status'])) {
            $validated['status'] = WidgetStatus::from($validated['status']);
        }

        return static::fromArray($validated);
    }
}

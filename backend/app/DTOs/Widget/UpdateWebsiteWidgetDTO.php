<?php

declare(strict_types=1);

namespace App\DTOs\Widget;

use App\DTOs\Concerns\MapsFromRequest;
use App\DTOs\DataTransferObject;
use App\Enums\WebsiteWidgetStatus;
use App\Http\Requests\ApiRequest;
use BackedEnum;

/**
 * Payload for updating a website widget installation.
 */
final class UpdateWebsiteWidgetDTO extends DataTransferObject
{
    use MapsFromRequest;

    /**
     * @param  array<string, mixed>|null  $configuration
     */
    public function __construct(
        public readonly ?WebsiteWidgetStatus $status = null,
        public readonly ?array $configuration = null,
    ) {}

    public static function fromRequest(ApiRequest $request): static
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        $status = null;
        if (isset($validated['status']) && is_string($validated['status'])) {
            $status = WebsiteWidgetStatus::from($validated['status']);
        }

        return new self(
            status: $status,
            configuration: array_key_exists('configuration', $validated)
                ? (array) $validated['configuration']
                : null,
        );
    }

    protected function transformValue(mixed $value): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        return parent::transformValue($value);
    }
}

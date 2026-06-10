<?php

declare(strict_types=1);

namespace App\DTOs\Widget;

use App\DTOs\Concerns\MapsFromRequest;
use App\DTOs\DataTransferObject;
use App\Http\Requests\ApiRequest;

/**
 * Payload for installing a widget on a customer website.
 */
final class InstallWidgetDTO extends DataTransferObject
{
    use MapsFromRequest;

    /**
     * @param  array<string, mixed>|null  $configuration
     */
    public function __construct(
        public readonly string $website_uuid,
        public readonly string $widget_version_uuid,
        public readonly ?array $configuration = null,
    ) {}

    public static function fromRequest(ApiRequest $request): static
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        return new self(
            website_uuid: (string) $validated['website_uuid'],
            widget_version_uuid: (string) $validated['widget_version_uuid'],
            configuration: isset($validated['configuration']) ? (array) $validated['configuration'] : null,
        );
    }
}

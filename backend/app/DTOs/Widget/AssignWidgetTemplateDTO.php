<?php

declare(strict_types=1);

namespace App\DTOs\Widget;

use App\DTOs\Concerns\MapsFromRequest;
use App\DTOs\DataTransferObject;
use App\Http\Requests\ApiRequest;

/**
 * Payload for assigning a template to a widget (`WidgetTemplateAssignmentService::assign`).
 */
final class AssignWidgetTemplateDTO extends DataTransferObject
{
    use MapsFromRequest;

    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $content,
        public readonly ?string $description = null,
        public readonly bool $is_default = false,
    ) {}

    public static function fromRequest(ApiRequest $request): static
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        return new self(
            name: (string) $validated['name'],
            slug: (string) $validated['slug'],
            content: (string) $validated['content'],
            description: isset($validated['description']) ? (string) $validated['description'] : null,
            is_default: (bool) ($validated['is_default'] ?? false),
        );
    }
}

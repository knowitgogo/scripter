<?php

declare(strict_types=1);

namespace App\DTOs\Widget;

use App\DTOs\Concerns\MapsFromRequest;
use App\DTOs\DataTransferObject;
use App\Http\Requests\ApiRequest;

/**
 * Payload for replacing a widget's category set (`WidgetCategoryService::sync`).
 */
final class SyncWidgetCategoriesDTO extends DataTransferObject
{
    use MapsFromRequest;

    /**
     * @param  list<string>  $category_uuids
     */
    public function __construct(
        public readonly array $category_uuids,
    ) {}

    public static function fromRequest(ApiRequest $request): static
    {
        /** @var list<string> $categoryUuids */
        $categoryUuids = $request->validated('category_uuids', []);

        return new self(category_uuids: $categoryUuids);
    }
}

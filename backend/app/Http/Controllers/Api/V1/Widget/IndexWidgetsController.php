<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Widget;

use App\DTOs\Widget\ListWidgetCatalogQueryDTO;
use App\DTOs\Widget\WidgetDTO;
use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Widget\ListWidgetsRequest;
use App\Services\Widget\WidgetService;
use Illuminate\Http\JsonResponse;

/**
 * Lists published widgets in the marketplace catalog.
 */
final class IndexWidgetsController extends BaseController
{
    public function __invoke(ListWidgetsRequest $request, WidgetService $service): JsonResponse
    {
        return $this->respondSuccess(
            array_map(
                fn (WidgetDTO $widget): array => $widget->toArray(),
                $service->listPublished(ListWidgetCatalogQueryDTO::fromRequest($request)),
            ),
        );
    }
}

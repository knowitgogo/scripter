<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Widget;

use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Widget\DeactivateWidgetRequest;
use App\Models\User;
use App\Services\Widget\WidgetService;
use Illuminate\Http\JsonResponse;

/**
 * Deactivates a widget in the marketplace catalog (admin).
 */
final class DeactivateWidgetController extends BaseController
{
    public function __invoke(
        DeactivateWidgetRequest $request,
        WidgetService $service,
        string $widget,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user('api');

        return $this->respondSuccess(
            $service->deactivate($widget, $user),
        );
    }
}

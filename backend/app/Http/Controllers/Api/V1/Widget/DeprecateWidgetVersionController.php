<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Widget;

use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Widget\DeprecateWidgetVersionRequest;
use App\Models\User;
use App\Services\Widget\WidgetVersionService;
use Illuminate\Http\JsonResponse;

/**
 * Deprecates a published widget version (admin).
 */
final class DeprecateWidgetVersionController extends BaseController
{
    public function __invoke(
        DeprecateWidgetVersionRequest $request,
        WidgetVersionService $service,
        string $widget_version,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user('api');

        return $this->respondSuccess(
            $service->deprecate($widget_version, $user),
        );
    }
}

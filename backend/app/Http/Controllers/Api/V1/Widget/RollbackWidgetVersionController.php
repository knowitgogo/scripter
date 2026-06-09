<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Widget;

use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Widget\RollbackWidgetVersionRequest;
use App\Models\User;
use App\Services\Widget\WidgetVersionService;
use Illuminate\Http\JsonResponse;

/**
 * Rolls back the marketplace catalog to a previously published widget version (admin).
 */
final class RollbackWidgetVersionController extends BaseController
{
    public function __invoke(
        RollbackWidgetVersionRequest $request,
        WidgetVersionService $service,
        string $widget_version,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user('api');

        return $this->respondSuccess(
            $service->rollback($widget_version, $user),
        );
    }
}

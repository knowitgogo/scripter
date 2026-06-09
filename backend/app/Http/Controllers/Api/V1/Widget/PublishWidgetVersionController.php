<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Widget;

use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Widget\PublishWidgetVersionRequest;
use App\Models\User;
use App\Services\Widget\WidgetVersionService;
use Illuminate\Http\JsonResponse;

/**
 * Publishes a widget version in the marketplace catalog (admin).
 */
final class PublishWidgetVersionController extends BaseController
{
    public function __invoke(
        PublishWidgetVersionRequest $request,
        WidgetVersionService $service,
        string $widget_version,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user('api');

        return $this->respondSuccess(
            $service->publish($widget_version, $user),
        );
    }
}

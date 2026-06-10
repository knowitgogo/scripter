<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Widget;

use App\DTOs\Widget\InstallWidgetDTO;
use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Widget\InstallWidgetRequest;
use App\Models\User;
use App\Services\Widget\WebsiteWidgetService;
use Illuminate\Http\JsonResponse;

/**
 * Installs a published widget version on a customer-owned website.
 */
final class StoreWebsiteWidgetController extends BaseController
{
    public function __invoke(InstallWidgetRequest $request, WebsiteWidgetService $service): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        return $this->respondCreated(
            $service->install(InstallWidgetDTO::fromRequest($request), $user),
        );
    }
}

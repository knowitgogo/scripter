<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Widget;

use App\DTOs\Widget\RegisterWidgetDTO;
use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Widget\RegisterWidgetRequest;
use App\Models\User;
use App\Services\Widget\WidgetService;
use Illuminate\Http\JsonResponse;

/**
 * Registers a widget in the marketplace catalog (admin).
 */
final class RegisterWidgetController extends BaseController
{
    public function __invoke(RegisterWidgetRequest $request, WidgetService $service): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        return $this->respondCreated(
            $service->register(RegisterWidgetDTO::fromRequest($request), $user),
        );
    }
}

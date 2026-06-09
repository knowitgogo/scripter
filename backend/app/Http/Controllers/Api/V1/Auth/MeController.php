<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Auth\CurrentUserRequest;
use App\Services\Auth\CurrentUserService;
use Illuminate\Http\JsonResponse;

/**
 * Returns the authenticated user's profile.
 */
final class MeController extends BaseController
{
    public function __invoke(CurrentUserRequest $request, CurrentUserService $service): JsonResponse
    {
        return $this->respondSuccess($service->get());
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Auth\LogoutRequest;
use App\Services\Auth\LogoutService;
use Illuminate\Http\JsonResponse;

/**
 * Invalidates the caller's JWT access token.
 */
final class LogoutController extends BaseController
{
    public function __invoke(LogoutRequest $request, LogoutService $service): JsonResponse
    {
        return $this->respondSuccess($service->logout());
    }
}

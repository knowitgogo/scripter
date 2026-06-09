<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Services\Auth\TokenRefreshService;
use Illuminate\Http\JsonResponse;

/**
 * Issues a new JWT access token for a valid refresh window.
 */
final class RefreshTokenController extends BaseController
{
    public function __invoke(RefreshTokenRequest $request, TokenRefreshService $service): JsonResponse
    {
        return $this->respondSuccess($service->refresh());
    }
}

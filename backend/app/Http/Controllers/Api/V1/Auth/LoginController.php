<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\DTOs\Auth\LoginDTO;
use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\LoginService;
use Illuminate\Http\JsonResponse;

/**
 * Issues a JWT access token for valid credentials.
 */
final class LoginController extends BaseController
{
    public function __invoke(LoginRequest $request, LoginService $service): JsonResponse
    {
        return $this->respondSuccess(
            $service->login(LoginDTO::fromRequest($request)),
        );
    }
}

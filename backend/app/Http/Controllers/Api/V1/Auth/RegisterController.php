<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\DTOs\Auth\RegisterDTO;
use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\RegisterService;
use Illuminate\Http\JsonResponse;

/**
 * Registers a new customer account and returns a JWT access token.
 */
final class RegisterController extends BaseController
{
    public function __invoke(RegisterRequest $request, RegisterService $service): JsonResponse
    {
        return $this->respondCreated(
            $service->register(RegisterDTO::fromRequest($request)),
        );
    }
}

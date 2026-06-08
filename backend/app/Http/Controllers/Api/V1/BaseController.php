<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTOs\DataTransferObject;
use App\Http\Controllers\Controller as AppController;
use App\Support\ApiResponse;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;

/**
 * Base controller for versioned API endpoints.
 *
 * Controllers must remain thin: validate, delegate to Services, return responses
 * via the helper methods below. No business logic or database access here.
 */
abstract class BaseController extends AppController
{
    /**
     * @param  array<string, mixed>|Arrayable<string, mixed>|DataTransferObject|null  $data
     */
    protected function respondSuccess(
        array|Arrayable|DataTransferObject|null $data = null,
        ?string $message = null,
        int $status = 200,
    ): JsonResponse {
        return ApiResponse::success($data, $message, $status);
    }

    /**
     * @param  array<string, mixed>|Arrayable<string, mixed>|DataTransferObject|null  $data
     */
    protected function respondCreated(
        array|Arrayable|DataTransferObject|null $data = null,
        ?string $message = null,
    ): JsonResponse {
        return ApiResponse::created($data, $message);
    }

    /**
     * @param  array<string, mixed>|Arrayable<string, mixed>|DataTransferObject|null  $data
     */
    protected function respondAccepted(
        array|Arrayable|DataTransferObject|null $data = null,
        ?string $message = null,
    ): JsonResponse {
        return ApiResponse::accepted($data, $message);
    }

    protected function respondNoContent(): JsonResponse
    {
        return ApiResponse::noContent();
    }

    /**
     * @param  array<int, string>  $errors
     */
    protected function respondError(
        ?string $message = null,
        array $errors = [],
        int $status = 400,
    ): JsonResponse {
        return ApiResponse::error($message, $errors, $status);
    }
}

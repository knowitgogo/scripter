<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\JsonResponse;

/**
 * Standard API response envelope per API_CONTRACTS_v3.
 */
final class ApiResponse
{
    /**
     * @param  array<string, mixed>|null  $data
     * @param  array<int, mixed>  $errors
     */
    public static function success(
        ?array $data = null,
        ?string $message = null,
        int $status = 200,
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $data ?? new \stdClass,
            'message' => $message,
            'errors' => [],
        ], $status);
    }

    /**
     * @param  array<string, mixed>|null  $data
     */
    public static function created(?array $data = null, ?string $message = null): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    /**
     * @param  array<int, mixed>  $errors
     */
    public static function error(
        ?string $message = null,
        array $errors = [],
        int $status = 400,
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'data' => new \stdClass,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}

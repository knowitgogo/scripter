<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;

/**
 * Standard API response envelope per API_CONTRACTS_v3.
 *
 * Every JSON response includes: success, data, message, errors.
 */
final class ApiResponse
{
    public const KEY_SUCCESS = 'success';

    public const KEY_DATA = 'data';

    public const KEY_MESSAGE = 'message';

    public const KEY_ERRORS = 'errors';

    /**
     * @param  array<string, mixed>|Arrayable<string, mixed>|null  $data
     */
    public static function success(
        array|Arrayable|null $data = null,
        ?string $message = null,
        int $status = 200,
    ): JsonResponse {
        return self::json(
            success: true,
            data: self::normalizeData($data),
            message: $message,
            errors: [],
            status: $status,
        );
    }

    /**
     * @param  array<string, mixed>|Arrayable<string, mixed>|null  $data
     */
    public static function created(
        array|Arrayable|null $data = null,
        ?string $message = null,
    ): JsonResponse {
        return self::success($data, $message, 201);
    }

    /**
     * @param  array<string, mixed>|Arrayable<string, mixed>|null  $data
     */
    public static function accepted(
        array|Arrayable|null $data = null,
        ?string $message = null,
    ): JsonResponse {
        return self::success($data, $message, 202);
    }

    public static function noContent(): JsonResponse
    {
        return response()->json([], 204);
    }

    /**
     * @param  array<int, string>  $errors
     */
    public static function error(
        ?string $message = null,
        array $errors = [],
        int $status = 400,
    ): JsonResponse {
        return self::json(
            success: false,
            data: new \stdClass,
            message: $message,
            errors: $errors,
            status: $status,
        );
    }

    /**
     * @param  array<string, mixed>|Arrayable<string, mixed>|\stdClass  $data
     * @param  array<int, string>  $errors
     */
    private static function json(
        bool $success,
        array|Arrayable|\stdClass $data,
        ?string $message,
        array $errors,
        int $status,
    ): JsonResponse {
        return response()->json([
            self::KEY_SUCCESS => $success,
            self::KEY_DATA => $data instanceof Arrayable ? self::normalizeData($data) : $data,
            self::KEY_MESSAGE => $message,
            self::KEY_ERRORS => $errors,
        ], $status);
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function normalizeData(array|Arrayable|null $data): array|\stdClass|null
    {
        if ($data === null) {
            return new \stdClass;
        }

        if ($data instanceof Arrayable) {
            return $data->toArray();
        }

        return $data;
    }
}

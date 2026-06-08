<?php

declare(strict_types=1);

namespace App\Support;

use App\Exceptions\DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Maps exceptions to the standard API response envelope for API routes.
 */
final class ApiExceptionRenderer
{
    public static function shouldHandle(Request $request): bool
    {
        return $request->is('api/*')
            || $request->is('up')
            || $request->expectsJson();
    }

    public static function render(Throwable $exception, Request $request): ?JsonResponse
    {
        if (! self::shouldHandle($request)) {
            return null;
        }

        if ($exception instanceof ValidationException) {
            return ApiResponse::error(
                message: $exception->getMessage() ?: 'Validation failed.',
                errors: self::flattenValidationErrors($exception),
                status: $exception->status,
            );
        }

        if ($exception instanceof AuthenticationException) {
            return ApiResponse::error(
                message: $exception->getMessage() ?: 'Unauthenticated.',
                status: 401,
            );
        }

        if ($exception instanceof AuthorizationException) {
            return ApiResponse::error(
                message: $exception->getMessage() ?: 'Forbidden.',
                status: 403,
            );
        }

        if ($exception instanceof ModelNotFoundException) {
            return ApiResponse::error(
                message: 'Resource not found.',
                status: 404,
            );
        }

        if ($exception instanceof DomainException) {
            return ApiResponse::error(
                message: $exception->getMessage(),
                status: $exception->statusCode(),
            );
        }

        if ($exception instanceof NotFoundHttpException) {
            return ApiResponse::error(
                message: 'Resource not found.',
                status: 404,
            );
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return ApiResponse::error(
                message: 'Method not allowed.',
                status: 405,
            );
        }

        if ($exception instanceof HttpExceptionInterface) {
            return ApiResponse::error(
                message: $exception->getMessage() ?: 'Request could not be processed.',
                status: $exception->getStatusCode(),
            );
        }

        return self::renderUnhandled($exception);
    }

    /**
     * @return array<int, string>
     */
    private static function flattenValidationErrors(ValidationException $exception): array
    {
        return collect($exception->errors())->flatten()->values()->all();
    }

    private static function renderUnhandled(Throwable $exception): JsonResponse
    {
        $traceId = UuidGenerator::generate();

        Log::error($exception->getMessage(), [
            'trace_id' => $traceId,
            'exception' => $exception,
        ]);

        $message = config('app.debug')
            ? $exception->getMessage()
            : 'An unexpected error occurred.';

        return ApiResponse::error($message, status: 500)
            ->header('X-Trace-Id', $traceId);
    }
}

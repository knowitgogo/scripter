<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * Infrastructure health endpoint. No domain Services involved.
 */
final class HealthController extends BaseController
{
    public function __invoke(): JsonResponse
    {
        return $this->respondSuccess([
            'status' => 'ok',
            'version' => 'v1',
        ]);
    }
}

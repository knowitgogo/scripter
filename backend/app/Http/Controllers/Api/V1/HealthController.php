<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Services\System\HealthCheckService;
use Illuminate\Http\JsonResponse;

/**
 * Liveness probe — confirms the API process is running.
 */
final class HealthController extends BaseController
{
    public function __invoke(HealthCheckService $service): JsonResponse
    {
        return $this->respondSuccess($service->check());
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Services\System\ReadinessCheckService;
use Illuminate\Http\JsonResponse;

/**
 * Readiness probe — confirms dependencies required to serve traffic are available.
 */
final class ReadinessController extends BaseController
{
    public function __invoke(ReadinessCheckService $service): JsonResponse
    {
        $readiness = $service->check();

        return $this->respondSuccess(
            $readiness,
            status: $readiness->isReady() ? 200 : 503,
        );
    }
}

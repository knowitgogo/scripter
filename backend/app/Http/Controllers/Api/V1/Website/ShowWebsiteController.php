<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Website;

use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Website\ShowWebsiteRequest;
use App\Models\User;
use App\Services\Website\WebsiteService;
use Illuminate\Http\JsonResponse;

/**
 * Returns a single website owned by the authenticated user.
 */
final class ShowWebsiteController extends BaseController
{
    public function __invoke(
        ShowWebsiteRequest $request,
        WebsiteService $service,
        string $website,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user('api');

        return $this->respondSuccess($service->getForUser($website, $user));
    }
}

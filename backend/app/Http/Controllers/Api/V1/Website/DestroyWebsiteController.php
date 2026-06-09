<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Website;

use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Website\DestroyWebsiteRequest;
use App\Models\User;
use App\Services\Website\WebsiteService;
use Illuminate\Http\JsonResponse;

/**
 * Deletes a website owned by the authenticated user.
 */
final class DestroyWebsiteController extends BaseController
{
    public function __invoke(
        DestroyWebsiteRequest $request,
        WebsiteService $service,
        string $website,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user('api');

        $service->delete($website, $user);

        return $this->respondNoContent();
    }
}

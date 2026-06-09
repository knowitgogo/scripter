<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Website;

use App\DTOs\Website\UpdateWebsiteDTO;
use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Website\UpdateWebsiteRequest;
use App\Models\User;
use App\Services\Website\WebsiteService;
use Illuminate\Http\JsonResponse;

/**
 * Updates a website owned by the authenticated user.
 */
final class UpdateWebsiteController extends BaseController
{
    public function __invoke(
        UpdateWebsiteRequest $request,
        WebsiteService $service,
        string $website,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user('api');

        return $this->respondSuccess(
            $service->update($website, UpdateWebsiteDTO::fromRequest($request), $user),
        );
    }
}

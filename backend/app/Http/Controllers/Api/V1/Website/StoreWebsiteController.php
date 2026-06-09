<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Website;

use App\DTOs\Website\CreateWebsiteDTO;
use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Website\CreateWebsiteRequest;
use App\Models\User;
use App\Services\Website\WebsiteService;
use Illuminate\Http\JsonResponse;

/**
 * Creates a website for the authenticated user.
 */
final class StoreWebsiteController extends BaseController
{
    public function __invoke(CreateWebsiteRequest $request, WebsiteService $service): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        return $this->respondCreated(
            $service->create(CreateWebsiteDTO::fromRequest($request), $user),
        );
    }
}

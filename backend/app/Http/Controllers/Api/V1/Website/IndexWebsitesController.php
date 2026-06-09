<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Website;

use App\DTOs\Website\WebsiteDTO;
use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Website\ListWebsitesRequest;
use App\Models\User;
use App\Services\Website\WebsiteService;
use Illuminate\Http\JsonResponse;

/**
 * Lists websites owned by the authenticated user.
 */
final class IndexWebsitesController extends BaseController
{
    public function __invoke(ListWebsitesRequest $request, WebsiteService $service): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        return $this->respondSuccess(
            array_map(
                fn (WebsiteDTO $website): array => $website->toArray(),
                $service->listForUser($user),
            ),
        );
    }
}

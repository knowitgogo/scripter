<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tag;

use App\DTOs\Tag\CreateTagDTO;
use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Tag\CreateTagRequest;
use App\Models\User;
use App\Services\Tag\TagService;
use Illuminate\Http\JsonResponse;

/**
 * Creates a reusable tag in the catalog.
 */
final class StoreTagController extends BaseController
{
    public function __invoke(CreateTagRequest $request, TagService $service): JsonResponse
    {
        /** @var User $user */
        $user = $request->user('api');

        return $this->respondCreated(
            $service->create(CreateTagDTO::fromRequest($request), $user),
        );
    }
}

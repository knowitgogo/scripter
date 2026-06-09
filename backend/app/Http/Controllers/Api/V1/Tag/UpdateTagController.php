<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tag;

use App\DTOs\Tag\UpdateTagDTO;
use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Models\User;
use App\Services\Tag\TagService;
use Illuminate\Http\JsonResponse;

/**
 * Updates a reusable tag in the catalog.
 */
final class UpdateTagController extends BaseController
{
    public function __invoke(
        UpdateTagRequest $request,
        TagService $service,
        string $tag,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user('api');

        return $this->respondSuccess(
            $service->update($tag, UpdateTagDTO::fromRequest($request), $user),
        );
    }
}

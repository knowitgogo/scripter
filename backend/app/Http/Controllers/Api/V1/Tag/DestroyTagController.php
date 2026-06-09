<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tag;

use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Tag\DestroyTagRequest;
use App\Models\User;
use App\Services\Tag\TagService;
use Illuminate\Http\JsonResponse;

/**
 * Deletes a reusable tag from the catalog.
 */
final class DestroyTagController extends BaseController
{
    public function __invoke(
        DestroyTagRequest $request,
        TagService $service,
        string $tag,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user('api');

        $service->delete($tag, $user);

        return $this->respondNoContent();
    }
}

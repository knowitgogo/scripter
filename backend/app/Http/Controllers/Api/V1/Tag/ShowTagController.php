<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tag;

use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Tag\ShowTagRequest;
use App\Services\Tag\TagService;
use Illuminate\Http\JsonResponse;

/**
 * Returns a single tag by public UUID.
 */
final class ShowTagController extends BaseController
{
    public function __invoke(ShowTagRequest $request, TagService $service, string $tag): JsonResponse
    {
        return $this->respondSuccess($service->getByUuid($tag));
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tag;

use App\DTOs\Tag\TagDTO;
use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Tag\ListTagsRequest;
use App\Services\Tag\TagService;
use Illuminate\Http\JsonResponse;

/**
 * Lists reusable tags ordered by name.
 */
final class IndexTagsController extends BaseController
{
    public function __invoke(ListTagsRequest $request, TagService $service): JsonResponse
    {
        return $this->respondSuccess(
            array_map(
                fn (TagDTO $tag): array => $tag->toArray(),
                $service->list(),
            ),
        );
    }
}

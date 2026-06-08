<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OpenApi\OpenApiSpecService;
use Illuminate\Http\Response;

/**
 * Serves the raw OpenAPI specification artifact.
 */
final class OpenApiSpecController extends Controller
{
    public function __invoke(OpenApiSpecService $service): Response
    {
        $document = $service->getDocument();

        return response($document->contents, 200, [
            'Content-Type' => 'application/yaml; charset=utf-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}

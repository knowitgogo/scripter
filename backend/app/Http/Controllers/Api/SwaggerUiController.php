<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Serves the Swagger UI documentation browser.
 */
final class SwaggerUiController extends Controller
{
    public function __invoke(): View
    {
        if (! config('openapi.ui_enabled')) {
            throw new NotFoundHttpException;
        }

        $specRoute = (string) config('openapi.routes.spec');

        return view('swagger.index', [
            'title' => (string) config('openapi.title'),
            'specUrl' => url('/api/'.$specRoute),
            'cdnVersion' => (string) config('openapi.swagger_ui.cdn_version'),
        ]);
    }
}

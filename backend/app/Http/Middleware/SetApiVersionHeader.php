<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\ApiVersion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Binds the active API version to the request and response headers.
 */
final class SetApiVersionHeader
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $version): Response
    {
        if (! ApiVersion::isSupported($version)) {
            abort(404, 'API version not supported.');
        }

        $request->attributes->set('api_version', $version);

        $response = $next($request);
        $response->headers->set(ApiVersion::versionHeader(), $version);

        return $response;
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Forces JSON negotiation for API routes regardless of Accept header.
 */
final class ForceJsonResponse
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldForceJson($request)) {
            return $next($request);
        }

        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }

    private function shouldForceJson(Request $request): bool
    {
        $specRoute = 'api/'.ltrim((string) config('openapi.routes.spec'), '/');
        $uiRoute = 'api/'.ltrim((string) config('openapi.routes.ui'), '/');

        return ! $request->is($specRoute, $uiRoute, $uiRoute.'/*');
    }
}

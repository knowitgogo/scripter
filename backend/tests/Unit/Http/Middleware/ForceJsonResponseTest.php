<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ForceJsonResponseTest extends TestCase
{
    #[Test]
    public function it_skips_openapi_documentation_routes(): void
    {
        $middleware = new ForceJsonResponse;
        $request = Request::create('/api/docs', 'GET', server: [
            'HTTP_ACCEPT' => 'text/html',
        ]);

        $response = $middleware->handle($request, fn (Request $request) => response('html'));

        $this->assertSame('text/html', $request->headers->get('Accept'));
        $this->assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function it_forces_json_for_versioned_api_routes(): void
    {
        $middleware = new ForceJsonResponse;
        $request = Request::create('/api/v1/health', 'GET', server: [
            'HTTP_ACCEPT' => 'text/html',
        ]);

        $capturedAccept = null;

        $middleware->handle($request, function (Request $req) use (&$capturedAccept) {
            $capturedAccept = $req->headers->get('Accept');

            return response()->json(['ok' => true]);
        });

        $this->assertSame('application/json', $capturedAccept);
    }
}

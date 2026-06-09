<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\SetApiVersionHeader;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SetApiVersionHeaderTest extends TestCase
{
    #[Test]
    public function it_sets_request_attribute_and_response_header(): void
    {
        config(['api.version_header' => 'X-API-Version']);

        $middleware = new SetApiVersionHeader;
        $request = Request::create('/api/v1/health', 'GET');

        $response = $middleware->handle($request, fn (Request $req) => response()->json(['ok' => true]), 'v1');

        $this->assertSame('v1', $request->attributes->get('api_version'));
        $this->assertSame('v1', $response->headers->get('X-API-Version'));
    }
}

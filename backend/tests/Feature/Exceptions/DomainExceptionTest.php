<?php

declare(strict_types=1);

namespace Tests\Feature\Exceptions;

use App\Exceptions\DomainException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DomainExceptionTest extends TestCase
{
    #[Test]
    public function domain_exception_renders_api_envelope(): void
    {
        $this->app->router->get('/api/v1/test-domain-exception', function (): void {
            throw new DomainException('Resource conflict.', 409);
        });

        $response = $this->getJson('/api/v1/test-domain-exception');

        $response->assertStatus(409);
        $response->assertJson([
            'success' => false,
            'message' => 'Resource conflict.',
            'errors' => [],
        ]);
    }
}

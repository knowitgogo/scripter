<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\ApiResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ApiResponseTest extends TestCase
{
    #[Test]
    public function success_returns_standard_envelope(): void
    {
        $response = ApiResponse::success(['id' => 'test']);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'success' => true,
            'data' => ['id' => 'test'],
            'message' => null,
            'errors' => [],
        ], $response->getData(true));
    }

    #[Test]
    public function created_returns_201_status(): void
    {
        $response = ApiResponse::created(['id' => 'test'], 'Created');

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame([
            'success' => true,
            'data' => ['id' => 'test'],
            'message' => 'Created',
            'errors' => [],
        ], $response->getData(true));
    }

    #[Test]
    public function error_returns_failure_envelope(): void
    {
        $response = ApiResponse::error('Failed', ['field is required'], 422);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'data' => [],
            'message' => 'Failed',
            'errors' => ['field is required'],
        ], $response->getData(true));
    }

    #[Test]
    public function success_without_data_returns_empty_object(): void
    {
        $response = ApiResponse::success();

        $data = $response->getData(true);

        $this->assertIsArray($data['data']);
        $this->assertSame([], $data['data']);
    }
}

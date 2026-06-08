<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\DTOs\DataTransferObject;
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
    public function success_accepts_arrayable_data(): void
    {
        $dto = new class ('abc-123') extends DataTransferObject
        {
            public function __construct(
                public readonly string $uuid,
            ) {}
        };

        $response = ApiResponse::success($dto);

        $this->assertSame([
            'success' => true,
            'data' => ['uuid' => 'abc-123'],
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
    public function accepted_returns_202_status(): void
    {
        $response = ApiResponse::accepted(['job' => 'queued'], 'Accepted');

        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame([
            'success' => true,
            'data' => ['job' => 'queued'],
            'message' => 'Accepted',
            'errors' => [],
        ], $response->getData(true));
    }

    #[Test]
    public function no_content_returns_204_without_envelope(): void
    {
        $response = ApiResponse::noContent();

        $this->assertSame(204, $response->getStatusCode());
        $this->assertSame('[]', $response->getContent());
        $this->assertArrayNotHasKey('success', $response->getData(true));
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

    #[Test]
    public function envelope_uses_standard_keys(): void
    {
        $response = ApiResponse::success(['ok' => true]);

        $payload = $response->getData(true);

        $this->assertSame([
            ApiResponse::KEY_SUCCESS,
            ApiResponse::KEY_DATA,
            ApiResponse::KEY_MESSAGE,
            ApiResponse::KEY_ERRORS,
        ], array_keys($payload));
    }
}

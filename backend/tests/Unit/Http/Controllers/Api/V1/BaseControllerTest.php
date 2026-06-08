<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\V1;

use App\DTOs\DataTransferObject;
use App\Http\Controllers\Api\V1\BaseController;
use Illuminate\Http\JsonResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class BaseControllerTest extends TestCase
{
    #[Test]
    public function respond_success_returns_standard_envelope(): void
    {
        $response = $this->controller()->exposeRespondSuccess(['uuid' => 'abc-123']);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'success' => true,
            'data' => ['uuid' => 'abc-123'],
            'message' => null,
            'errors' => [],
        ], $response->getData(true));
    }

    #[Test]
    public function respond_created_returns_201(): void
    {
        $response = $this->controller()->exposeRespondCreated(['uuid' => 'abc-123'], 'Created');

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('Created', $response->getData(true)['message']);
    }

    #[Test]
    public function respond_accepted_returns_202(): void
    {
        $response = $this->controller()->exposeRespondAccepted(['status' => 'queued']);

        $this->assertSame(202, $response->getStatusCode());
    }

    #[Test]
    public function respond_no_content_returns_204(): void
    {
        $response = $this->controller()->exposeRespondNoContent();

        $this->assertSame(204, $response->getStatusCode());
    }

    #[Test]
    public function respond_error_returns_failure_envelope(): void
    {
        $response = $this->controller()->exposeRespondError('Invalid input.', ['name required'], 422);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'data' => [],
            'message' => 'Invalid input.',
            'errors' => ['name required'],
        ], $response->getData(true));
    }

    #[Test]
    public function respond_success_accepts_dto(): void
    {
        $dto = new class ('abc-123', 'active') extends DataTransferObject
        {
            public function __construct(
                public readonly string $uuid,
                public readonly string $status,
            ) {}
        };

        $response = $this->controller()->exposeRespondSuccess($dto);

        $this->assertSame([
            'uuid' => 'abc-123',
            'status' => 'active',
        ], $response->getData(true)['data']);
    }

    private function controller(): TestableBaseController
    {
        return new TestableBaseController;
    }
}

/**
 * @internal
 */
final class TestableBaseController extends BaseController
{
    /**
     * @param  array<string, mixed>|DataTransferObject|null  $data
     */
    public function exposeRespondSuccess(array|DataTransferObject|null $data = null): JsonResponse
    {
        return $this->respondSuccess($data);
    }

    /**
     * @param  array<string, mixed>|DataTransferObject|null  $data
     */
    public function exposeRespondCreated(array|DataTransferObject|null $data = null, ?string $message = null): JsonResponse
    {
        return $this->respondCreated($data, $message);
    }

    /**
     * @param  array<string, mixed>|DataTransferObject|null  $data
     */
    public function exposeRespondAccepted(array|DataTransferObject|null $data = null): JsonResponse
    {
        return $this->respondAccepted($data);
    }

    public function exposeRespondNoContent(): JsonResponse
    {
        return $this->respondNoContent();
    }

    public function exposeRespondError(?string $message = null, array $errors = [], int $status = 400): JsonResponse
    {
        return $this->respondError($message, $errors, $status);
    }
}

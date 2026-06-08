<?php

declare(strict_types=1);

namespace Tests\Unit\Services\OpenApi;

use App\Repositories\Contracts\OpenApiSpecRepositoryInterface;
use App\Services\OpenApi\OpenApiSpecService;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class OpenApiSpecServiceTest extends TestCase
{
    #[Test]
    public function it_returns_document_dto_from_repository(): void
    {
        $contents = <<<'YAML'
openapi: 3.1.0
info:
  title: Script Manager API
  version: 1.0.0
paths:
  /health:
    get:
      responses:
        '200':
          description: ok
components:
  schemas:
    ApiEnvelope:
      type: object
YAML;

        $repository = $this->createMock(OpenApiSpecRepositoryInterface::class);
        $repository->method('read')->willReturn($contents);

        $document = (new OpenApiSpecService($repository))->getDocument();

        $this->assertSame('3.1.0', $document->openapi);
        $this->assertSame('Script Manager API', $document->title);
        $this->assertSame('1.0.0', $document->version);
        $this->assertSame('yaml', $document->format);
        $this->assertSame($contents, $document->contents);
    }

    #[Test]
    public function it_rejects_invalid_specification(): void
    {
        $repository = $this->createMock(OpenApiSpecRepositoryInterface::class);
        $repository->method('read')->willReturn('title: invalid');

        $this->expectException(InvalidArgumentException::class);

        (new OpenApiSpecService($repository))->getDocument();
    }
}

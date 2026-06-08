<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\OpenApi;

use App\Repositories\OpenApi\FileOpenApiSpecRepository;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

final class FileOpenApiSpecRepositoryTest extends TestCase
{
    #[Test]
    public function it_reads_existing_specification_file(): void
    {
        $repository = new FileOpenApiSpecRepository;

        $this->assertTrue($repository->exists());
        $this->assertStringContainsString('openapi: 3.1.0', $repository->read());
    }

    #[Test]
    public function it_throws_when_specification_file_is_missing(): void
    {
        config(['openapi.spec_path' => base_path('openapi/missing.yaml')]);

        $repository = new FileOpenApiSpecRepository;

        $this->assertFalse($repository->exists());

        $this->expectException(RuntimeException::class);

        $repository->read();
    }
}

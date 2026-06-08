<?php

declare(strict_types=1);

namespace App\Repositories\OpenApi;

use App\Repositories\Contracts\OpenApiSpecRepositoryInterface;
use RuntimeException;

/**
 * Loads the OpenAPI specification from the filesystem.
 */
final class FileOpenApiSpecRepository implements OpenApiSpecRepositoryInterface
{
    public function exists(): bool
    {
        return is_file($this->path());
    }

    public function read(): string
    {
        if (! $this->exists()) {
            throw new RuntimeException('OpenAPI specification file not found.');
        }

        $contents = file_get_contents($this->path());

        if ($contents === false || trim($contents) === '') {
            throw new RuntimeException('OpenAPI specification file is unreadable or empty.');
        }

        return $contents;
    }

    private function path(): string
    {
        return (string) config('openapi.spec_path');
    }
}

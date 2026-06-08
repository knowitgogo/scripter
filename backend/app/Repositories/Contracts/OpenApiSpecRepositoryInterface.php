<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

/**
 * Reads the OpenAPI specification artifact from storage.
 */
interface OpenApiSpecRepositoryInterface extends RepositoryInterface
{
    public function exists(): bool;

    public function read(): string;
}

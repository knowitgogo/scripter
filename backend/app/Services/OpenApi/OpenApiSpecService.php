<?php

declare(strict_types=1);

namespace App\Services\OpenApi;

use App\DTOs\OpenApi\OpenApiDocumentDTO;
use App\Repositories\Contracts\OpenApiSpecRepositoryInterface;
use InvalidArgumentException;

/**
 * Provides access to the OpenAPI specification artifact.
 */
final class OpenApiSpecService
{
    public function __construct(
        private readonly OpenApiSpecRepositoryInterface $specRepository,
    ) {}

    public function getDocument(): OpenApiDocumentDTO
    {
        $contents = $this->specRepository->read();

        $this->validate($contents);

        return new OpenApiDocumentDTO(
            openapi: $this->extractOpenApiVersion($contents),
            title: $this->extractInfoField($contents, 'title') ?? (string) config('openapi.title'),
            version: $this->extractInfoField($contents, 'version') ?? (string) config('openapi.version'),
            contents: $contents,
            format: 'yaml',
        );
    }

    public function isAvailable(): bool
    {
        return $this->specRepository->exists();
    }

    private function validate(string $contents): void
    {
        if (! str_contains($contents, 'openapi:')) {
            throw new InvalidArgumentException('OpenAPI specification is missing the openapi version field.');
        }

        if (! str_contains($contents, 'paths:')) {
            throw new InvalidArgumentException('OpenAPI specification is missing the paths section.');
        }

        if (! str_contains($contents, 'components:')) {
            throw new InvalidArgumentException('OpenAPI specification is missing the components section.');
        }
    }

    private function extractOpenApiVersion(string $contents): string
    {
        if (preg_match('/^openapi:\s*([^\n\r]+)/m', $contents, $matches) === 1) {
            return trim($matches[1]);
        }

        return '3.1.0';
    }

    private function extractInfoField(string $contents, string $field): ?string
    {
        $pattern = sprintf('/^\s{2}%s:\s*(.+)$/m', preg_quote($field, '/'));

        if (preg_match($pattern, $contents, $matches) === 1) {
            return trim($matches[1]);
        }

        return null;
    }
}

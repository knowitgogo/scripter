<?php

declare(strict_types=1);

namespace Tests\Contract\OpenApi;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WebsiteTagOpenApiSpecTest extends TestCase
{
    private string $specContents;

    protected function setUp(): void
    {
        parent::setUp();

        $path = base_path('openapi/openapi.yaml');
        $this->assertFileExists($path);

        $this->specContents = (string) file_get_contents($path);
    }

    #[Test]
    public function openapi_spec_defines_website_tags_schema(): void
    {
        $this->assertStringContainsString('WebsiteTags:', $this->specContents);
        $this->assertStringContainsString('SyncWebsiteTagsRequest:', $this->specContents);
    }

    #[Test]
    public function openapi_spec_documents_website_tags_pivot(): void
    {
        $this->assertStringContainsString('website_tags', $this->specContents);
        $this->assertStringContainsString('WebsiteTagService', $this->specContents);
    }
}

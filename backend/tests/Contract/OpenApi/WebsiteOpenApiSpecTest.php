<?php

declare(strict_types=1);

namespace Tests\Contract\OpenApi;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WebsiteOpenApiSpecTest extends TestCase
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
    public function openapi_spec_defines_website_collection_endpoint(): void
    {
        $this->assertStringContainsString('/websites:', $this->specContents);
        $this->assertStringContainsString('operationId: listWebsites', $this->specContents);
        $this->assertStringContainsString('operationId: createWebsite', $this->specContents);
    }

    #[Test]
    public function openapi_spec_defines_website_item_endpoint(): void
    {
        $this->assertStringContainsString('/websites/{website}:', $this->specContents);
        $this->assertStringContainsString('operationId: showWebsite', $this->specContents);
        $this->assertStringContainsString('operationId: updateWebsite', $this->specContents);
        $this->assertStringContainsString('operationId: deleteWebsite', $this->specContents);
    }

    #[Test]
    public function openapi_spec_defines_website_schemas(): void
    {
        $this->assertStringContainsString('Website:', $this->specContents);
        $this->assertStringContainsString('CreateWebsiteRequest:', $this->specContents);
        $this->assertStringContainsString('UpdateWebsiteRequest:', $this->specContents);
        $this->assertStringContainsString('ListWebsitesQuery:', $this->specContents);
        $this->assertStringContainsString('WebsiteStatus:', $this->specContents);
    }

    #[Test]
    public function openapi_spec_documents_website_list_tag_filter(): void
    {
        $this->assertStringContainsString('tag_uuids', $this->specContents);
        $this->assertStringContainsString('Filter by tag UUIDs', $this->specContents);
    }

    #[Test]
    public function openapi_spec_documents_website_security_and_error_responses(): void
    {
        $this->assertStringContainsString('Missing websites.view permission', $this->specContents);
        $this->assertStringContainsString('Missing websites.manage permission', $this->specContents);
        $this->assertStringContainsString('Website not found', $this->specContents);
    }

    #[Test]
    public function openapi_spec_tags_website_operations(): void
    {
        $this->assertStringContainsString('- name: Websites', $this->specContents);
        $this->assertStringContainsString('websites.view', $this->specContents);
        $this->assertStringContainsString('websites.manage', $this->specContents);
    }
}

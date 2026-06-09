<?php

declare(strict_types=1);

namespace Tests\Contract\OpenApi;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TagOpenApiSpecTest extends TestCase
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
    public function openapi_spec_defines_tag_collection_endpoint(): void
    {
        $this->assertStringContainsString('/tags:', $this->specContents);
        $this->assertStringContainsString('operationId: listTags', $this->specContents);
        $this->assertStringContainsString('operationId: createTag', $this->specContents);
    }

    #[Test]
    public function openapi_spec_defines_tag_item_endpoint(): void
    {
        $this->assertStringContainsString('/tags/{tag}:', $this->specContents);
        $this->assertStringContainsString('operationId: showTag', $this->specContents);
        $this->assertStringContainsString('operationId: updateTag', $this->specContents);
        $this->assertStringContainsString('operationId: deleteTag', $this->specContents);
    }

    #[Test]
    public function openapi_spec_defines_tag_schemas(): void
    {
        $this->assertStringContainsString('Tag:', $this->specContents);
        $this->assertStringContainsString('CreateTagRequest:', $this->specContents);
        $this->assertStringContainsString('UpdateTagRequest:', $this->specContents);
        $this->assertStringContainsString('Public tag response (`TagDTO`)', $this->specContents);
    }

    #[Test]
    public function openapi_spec_documents_tag_security_and_error_responses(): void
    {
        $this->assertStringContainsString('Missing tags.view permission', $this->specContents);
        $this->assertStringContainsString('Missing tags.manage permission', $this->specContents);
        $this->assertStringContainsString('Tag not found', $this->specContents);
    }

    #[Test]
    public function openapi_spec_tags_domain_is_documented(): void
    {
        $this->assertStringContainsString('- name: Tags', $this->specContents);
        $this->assertStringContainsString('website_tags', $this->specContents);
        $this->assertStringContainsString('TagService', $this->specContents);
        $this->assertStringContainsString('tags.view', $this->specContents);
        $this->assertStringContainsString('tags.manage', $this->specContents);
    }

    #[Test]
    public function openapi_spec_documents_tag_attachment_schemas(): void
    {
        $this->assertStringContainsString('WebsiteTags:', $this->specContents);
        $this->assertStringContainsString('SyncWebsiteTagsRequest:', $this->specContents);
    }
}

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
    public function openapi_spec_defines_tag_schema(): void
    {
        $this->assertStringContainsString('Tag:', $this->specContents);
        $this->assertStringContainsString('Public tag response (`TagDTO`)', $this->specContents);
    }

    #[Test]
    public function openapi_spec_tags_domain_is_documented(): void
    {
        $this->assertStringContainsString('- name: Tags', $this->specContents);
        $this->assertStringContainsString('website_tag', $this->specContents);
    }
}

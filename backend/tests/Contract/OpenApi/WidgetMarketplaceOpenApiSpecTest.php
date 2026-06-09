<?php

declare(strict_types=1);

namespace Tests\Contract\OpenApi;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetMarketplaceOpenApiSpecTest extends TestCase
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
    public function openapi_spec_defines_widgets_tag(): void
    {
        $this->assertStringContainsString('- name: Widgets', $this->specContents);
        $this->assertStringContainsString('widgets.view', $this->specContents);
        $this->assertStringContainsString('widgets.install', $this->specContents);
    }

    #[Test]
    public function openapi_spec_defines_widget_marketplace_schemas(): void
    {
        $this->assertStringContainsString('Widget:', $this->specContents);
        $this->assertStringContainsString('WidgetVersion:', $this->specContents);
        $this->assertStringContainsString('WidgetVersionStatus:', $this->specContents);
        $this->assertStringContainsString('WebsiteWidget:', $this->specContents);
        $this->assertStringContainsString('InstallWidgetRequest:', $this->specContents);
        $this->assertStringContainsString('UpdateWebsiteWidgetRequest:', $this->specContents);
        $this->assertStringContainsString('WidgetKey:', $this->specContents);
        $this->assertStringContainsString('CreateWidgetKeyRequest:', $this->specContents);
        $this->assertStringContainsString('WidgetInitConfig:', $this->specContents);
    }

    #[Test]
    public function openapi_spec_documents_widget_permissions(): void
    {
        $this->assertStringContainsString('admin.widgets.publish', $this->specContents);
    }
}

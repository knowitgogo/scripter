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
        $this->assertStringContainsString('ListWidgetCatalogQuery:', $this->specContents);
    }

    #[Test]
    public function openapi_spec_documents_widget_catalog_paths(): void
    {
        $this->assertStringContainsString('/widgets:', $this->specContents);
        $this->assertStringContainsString('/widgets/{widget}:', $this->specContents);
        $this->assertStringContainsString('operationId: listWidgets', $this->specContents);
        $this->assertStringContainsString('operationId: showWidget', $this->specContents);
        $this->assertStringContainsString('operationId: registerWidget', $this->specContents);
        $this->assertStringContainsString('operationId: activateWidget', $this->specContents);
        $this->assertStringContainsString('operationId: deactivateWidget', $this->specContents);
        $this->assertStringContainsString('operationId: publishWidgetVersion', $this->specContents);
        $this->assertStringContainsString('operationId: deprecateWidgetVersion', $this->specContents);
        $this->assertStringContainsString('operationId: rollbackWidgetVersion', $this->specContents);
    }

    #[Test]
    public function openapi_spec_documents_widget_version_rollback_path(): void
    {
        $this->assertStringContainsString('/widget-versions/{widget_version}/rollback:', $this->specContents);
    }

    #[Test]
    public function openapi_spec_documents_widget_version_publishing_paths(): void
    {
        $this->assertStringContainsString('/widget-versions/{widget_version}/publish:', $this->specContents);
        $this->assertStringContainsString('/widget-versions/{widget_version}/deprecate:', $this->specContents);
    }

    #[Test]
    public function openapi_spec_documents_widget_registration_schema(): void
    {
        $this->assertStringContainsString('RegisterWidgetRequest:', $this->specContents);
        $this->assertStringContainsString('Missing admin.widgets.publish permission', $this->specContents);
    }

    #[Test]
    public function openapi_spec_documents_widget_permissions(): void
    {
        $this->assertStringContainsString('admin.widgets.publish', $this->specContents);
    }
}

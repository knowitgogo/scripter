<?php

declare(strict_types=1);

namespace Tests\Unit\Architecture;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WidgetMarketplaceArchitectureDocTest extends TestCase
{
    private string $docContents;

    protected function setUp(): void
    {
        parent::setUp();

        $path = dirname(__DIR__, 4).'/docs/WIDGET_MARKETPLACE_ARCHITECTURE.md';
        $this->assertFileExists($path);

        $this->docContents = (string) file_get_contents($path);
    }

    #[Test]
    public function architecture_doc_exists_and_declares_widget_domain(): void
    {
        $this->assertStringContainsString('# Widget Marketplace Architecture', $this->docContents);
        $this->assertStringContainsString('WidgetCatalogService', $this->docContents);
        $this->assertStringContainsString('WebsiteWidgetService', $this->docContents);
        $this->assertStringContainsString('WidgetKeyService', $this->docContents);
    }

    #[Test]
    public function architecture_doc_documents_repository_service_dto_pattern(): void
    {
        $this->assertStringContainsString('Repository', $this->docContents);
        $this->assertStringContainsString('Form Request', $this->docContents);
        $this->assertStringContainsString('WidgetDTO', $this->docContents);
        $this->assertStringContainsString('InstallWidgetDTO', $this->docContents);
    }

    #[Test]
    public function architecture_doc_documents_permissions_and_testing(): void
    {
        $this->assertStringContainsString('widgets.view', $this->docContents);
        $this->assertStringContainsString('widgets.install', $this->docContents);
        $this->assertStringContainsString('PHPUnit', $this->docContents);
        $this->assertStringContainsString('OpenAPI', $this->docContents);
    }

    #[Test]
    public function architecture_doc_documents_data_model_and_runtime_flow(): void
    {
        $this->assertStringContainsString('widget_versions', $this->docContents);
        $this->assertStringContainsString('website_widgets', $this->docContents);
        $this->assertStringContainsString('widget_keys', $this->docContents);
        $this->assertStringContainsString('/widget/initialize', $this->docContents);
    }
}

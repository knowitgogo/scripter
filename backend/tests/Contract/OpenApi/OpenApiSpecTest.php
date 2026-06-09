<?php

declare(strict_types=1);

namespace Tests\Contract\OpenApi;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class OpenApiSpecTest extends TestCase
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
    public function openapi_spec_uses_version_3_1(): void
    {
        $this->assertStringContainsString('openapi: 3.1.0', $this->specContents);
    }

    #[Test]
    public function openapi_spec_defines_login_endpoint(): void
    {
        $this->assertStringContainsString('/auth/login:', $this->specContents);
        $this->assertStringContainsString('operationId: login', $this->specContents);
        $this->assertStringContainsString('LoginRequest:', $this->specContents);
    }

    #[Test]
    public function openapi_spec_defines_health_endpoint(): void
    {
        $this->assertStringContainsString('/health:', $this->specContents);
        $this->assertStringContainsString('operationId: healthCheck', $this->specContents);
    }

    #[Test]
    public function openapi_spec_defines_readiness_endpoint(): void
    {
        $this->assertStringContainsString('/ready:', $this->specContents);
        $this->assertStringContainsString('operationId: readinessCheck', $this->specContents);
    }

    #[Test]
    public function openapi_spec_defines_documentation_routes(): void
    {
        $this->assertStringContainsString('/openapi.yaml:', $this->specContents);
        $this->assertStringContainsString('/docs:', $this->specContents);
        $this->assertStringContainsString('operationId: swaggerUi', $this->specContents);
    }

    #[Test]
    public function openapi_spec_defines_api_envelope_schema(): void
    {
        $this->assertStringContainsString('ApiEnvelope:', $this->specContents);
        $this->assertStringContainsString('success:', $this->specContents);
        $this->assertStringContainsString('errors:', $this->specContents);
    }

    #[Test]
    public function openapi_spec_defines_role_schema(): void
    {
        $this->assertStringContainsString('Role:', $this->specContents);
        $this->assertStringContainsString('enum: [customer, admin, super_admin]', $this->specContents);
    }

    #[Test]
    public function openapi_spec_defines_user_and_assign_role_schemas(): void
    {
        $this->assertStringContainsString('User:', $this->specContents);
        $this->assertStringContainsString('AssignRoleRequest:', $this->specContents);
    }

    #[Test]
    public function openapi_spec_defines_jwt_security_and_auth_token_schema(): void
    {
        $this->assertStringContainsString('bearerAuth:', $this->specContents);
        $this->assertStringContainsString('bearerFormat: JWT', $this->specContents);
        $this->assertStringContainsString('AuthToken:', $this->specContents);
    }
}

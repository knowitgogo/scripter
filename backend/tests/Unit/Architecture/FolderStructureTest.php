<?php

declare(strict_types=1);

namespace Tests\Unit\Architecture;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FolderStructureTest extends TestCase
{
    /**
     * @return array<string, array{0: string}>
     */
    public static function requiredDirectories(): array
    {
        $base = dirname(__DIR__, 3);

        $directories = [
            'app/DTOs/Auth',
            'app/DTOs/Website',
            'app/DTOs/Widget',
            'app/DTOs/Analytics',
            'app/DTOs/Billing',
            'app/Services/Auth',
            'app/Services/Website',
            'app/Services/Widget',
            'app/Services/Analytics',
            'app/Services/Billing',
            'app/Services/Admin',
            'app/Repositories/Contracts',
            'app/Repositories/Eloquent',
            'app/Events/Audit',
            'app/Events/Audit/Contracts',
            'app/Listeners',
            'app/Jobs',
            'app/Services/Audit',
            'app/Repositories/Audit',
            'app/DTOs/Audit',
            'app/Policies',
            'app/Enums',
            'app/Support',
            'app/Exceptions',
            'app/Http/Controllers/Api/V1',
            'app/Http/Requests/Auth',
            'app/Http/Requests/Website',
            'app/Http/Requests/Widget',
            'app/Http/Requests/Analytics',
            'app/Http/Requests/Billing',
            'app/Http/Requests/Admin',
            'app/Http/Resources',
            'openapi',
            'routes/api',
            'tests/Unit',
            'tests/Feature',
            'tests/Contract',
            'tests/Concerns',
            'tests/Feature/Auth',
            'tests/Feature/Api/V1/Auth',
        ];

        $cases = [];
        foreach ($directories as $directory) {
            $cases[$directory] = [$base.'/'.$directory];
        }

        return $cases;
    }

    #[Test]
    #[DataProvider('requiredDirectories')]
    public function required_directory_exists(string $path): void
    {
        $this->assertDirectoryExists($path);
    }

    #[Test]
    public function base_infrastructure_classes_exist(): void
    {
        $base = dirname(__DIR__, 3).'/app';

        $files = [
            'DTOs/DataTransferObject.php',
            'DTOs/Concerns/MapsFromRequest.php',
            'DTOs/Concerns/MapsFromModel.php',
            'Support/ApiResponse.php',
            'Support/ApiVersion.php',
            'Http/Middleware/SetApiVersionHeader.php',
            'Support/ApiExceptionRenderer.php',
            'Support/UuidGenerator.php',
            'Http/Middleware/ForceJsonResponse.php',
            'Repositories/Contracts/RepositoryInterface.php',
            'Repositories/Contracts/EloquentRepositoryInterface.php',
            'Repositories/Eloquent/EloquentRepository.php',
            'Repositories/Eloquent/UuidEloquentRepository.php',
            'Http/Controllers/Api/V1/BaseController.php',
            'Http/Requests/ApiRequest.php',
            'Models/Concerns/HasUuid.php',
            'Models/Concerns/HidesInternalId.php',
            'Models/PublicEntity.php',
            'Repositories/Contracts/UuidRepositoryInterface.php',
            'Repositories/Concerns/FindsByUuid.php',
            'Repositories/Cache/LaravelCacheRepository.php',
            'Repositories/Queue/LaravelQueueDispatcher.php',
            'Repositories/Contracts/CacheRepositoryInterface.php',
            'Repositories/Contracts/QueueDispatcherInterface.php',
            'Services/Infrastructure/CacheService.php',
            'Services/Infrastructure/QueueService.php',
            'Services/OpenApi/OpenApiSpecService.php',
            'Repositories/OpenApi/FileOpenApiSpecRepository.php',
            'Repositories/Contracts/OpenApiSpecRepositoryInterface.php',
            'DTOs/OpenApi/OpenApiDocumentDTO.php',
            'Support/Cache/CacheKeyBuilder.php',
            'Rules/ValidUuid.php',
            'Support/Database/BlueprintMacros.php',
            'Exceptions/DomainException.php',
            'Support/Auth/JwtClaimBuilder.php',
            'DTOs/Auth/AuthTokenDTO.php',
            'DTOs/Auth/LoginDTO.php',
            'DTOs/Auth/RegisterDTO.php',
            'Http/Requests/Auth/RegisterRequest.php',
            'Http/Controllers/Api/V1/Auth/RegisterController.php',
            'Services/Auth/RegisterService.php',
            'Http/Requests/Auth/LoginRequest.php',
            'Http/Controllers/Api/V1/Auth/LoginController.php',
            'Services/Auth/LoginService.php',
            'Services/Auth/LogoutService.php',
            'Services/Auth/TokenRefreshService.php',
            'Http/Requests/Auth/RefreshTokenRequest.php',
            'Http/Controllers/Api/V1/Auth/RefreshTokenController.php',
            'Services/Auth/CurrentUserService.php',
            'Http/Requests/Auth/CurrentUserRequest.php',
            'Http/Controllers/Api/V1/Auth/MeController.php',
            'DTOs/Auth/LogoutResultDTO.php',
            'Http/Requests/Auth/LogoutRequest.php',
            'Http/Controllers/Api/V1/Auth/LogoutController.php',
            'Enums/AuditAction.php',
            'Events/Audit/AbstractAuditEvent.php',
            'Events/Audit/GenericAuditEvent.php',
            'Events/Audit/Contracts/AuditEventInterface.php',
            'Listeners/RecordAuditLog.php',
            'Jobs/PersistAuditLogJob.php',
            'Services/Audit/AuditLogService.php',
            'Services/Audit/AuditDispatcher.php',
            'Repositories/Audit/EloquentAuditLogRepository.php',
            'Repositories/Contracts/AuditLogRepositoryInterface.php',
            'DTOs/Audit/AuditLogEntryDTO.php',
            'Models/AuditLog.php',
            'Providers/EventServiceProvider.php',
            'Providers/RepositoryServiceProvider.php',
        ];

        foreach ($files as $file) {
            $this->assertFileExists($base.'/'.$file);
        }
    }

    #[Test]
    public function authentication_test_suite_files_exist(): void
    {
        $base = dirname(__DIR__, 3).'/tests';

        $files = [
            'Concerns/InteractsWithAuthentication.php',
            'Feature/Auth/AuthenticationFlowTest.php',
            'Feature/Auth/AuthenticationGuardTest.php',
            'Unit/Auth/AuthenticationHelpersTest.php',
        ];

        foreach ($files as $file) {
            $this->assertFileExists($base.'/'.$file);
        }
    }
}

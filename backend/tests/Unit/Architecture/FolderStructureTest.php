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
            'app/DTOs/Tag',
            'app/DTOs/Widget',
            'app/DTOs/Analytics',
            'app/DTOs/Billing',
            'app/Services/Auth',
            'app/Services/Website',
            'app/Services/Tag',
            'app/Services/Widget',
            'app/Services/Analytics',
            'app/Services/Billing',
            'app/Services/Admin',
            'app/Repositories/Contracts',
            'app/Repositories/Eloquent',
            'app/Repositories/Permissions',
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
            'app/Http/Requests/Tag',
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
            'Enums/Permission.php',
            'DTOs/Auth/UserPermissionsDTO.php',
            'Repositories/Contracts/PermissionsRepositoryInterface.php',
            'Repositories/Permissions/ConfigPermissionsRepository.php',
            'Services/Auth/PermissionService.php',
            'Services/Auth/AuthorizationService.php',
            'Policies/BasePolicy.php',
            'Policies/Concerns/ChecksPermissions.php',
            'Http/Middleware/EnsurePermission.php',
            'Providers/AuthorizationServiceProvider.php',
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
    public function website_domain_files_exist(): void
    {
        $app = dirname(__DIR__, 3).'/app';
        $tests = dirname(__DIR__, 3).'/tests';

        $files = [
            $app.'/Enums/WebsiteStatus.php',
            $app.'/Models/Website.php',
            $app.'/Repositories/Contracts/WebsiteRepositoryInterface.php',
            $app.'/Repositories/Eloquent/EloquentWebsiteRepository.php',
            $app.'/DTOs/Website/WebsiteDTO.php',
            $app.'/DTOs/Website/CreateWebsiteDTO.php',
            $app.'/DTOs/Website/UpdateWebsiteDTO.php',
            $app.'/DTOs/Website/ListWebsitesQueryDTO.php',
            $app.'/Services/Website/WebsiteService.php',
            $app.'/Http/Controllers/Api/V1/Website/IndexWebsitesController.php',
            $app.'/Http/Controllers/Api/V1/Website/StoreWebsiteController.php',
            $app.'/Http/Controllers/Api/V1/Website/ShowWebsiteController.php',
            $app.'/Http/Controllers/Api/V1/Website/UpdateWebsiteController.php',
            $app.'/Http/Controllers/Api/V1/Website/DestroyWebsiteController.php',
            $app.'/Http/Requests/Website/ListWebsitesRequest.php',
            $app.'/Http/Requests/Website/CreateWebsiteRequest.php',
            $app.'/Http/Requests/Website/ShowWebsiteRequest.php',
            $app.'/Http/Requests/Website/UpdateWebsiteRequest.php',
            $app.'/Http/Requests/Website/DestroyWebsiteRequest.php',
            dirname(__DIR__, 3).'/database/factories/WebsiteFactory.php',
            dirname(__DIR__, 3).'/database/migrations/2026_06_08_160000_create_websites_table.php',
            $tests.'/Unit/Database/WebsitesMigrationTest.php',
            $tests.'/Unit/Enums/WebsiteStatusTest.php',
            $tests.'/Feature/Models/WebsiteModelTest.php',
            $tests.'/Unit/Repositories/Eloquent/EloquentWebsiteRepositoryTest.php',
            $tests.'/Unit/DTOs/Website/WebsiteDTOTest.php',
            $tests.'/Unit/DTOs/Website/CreateWebsiteDTOTest.php',
            $tests.'/Unit/DTOs/Website/UpdateWebsiteDTOTest.php',
            $tests.'/Unit/DTOs/Website/ListWebsitesQueryDTOTest.php',
            $tests.'/Concerns/InteractsWithWebsites.php',
            $tests.'/Unit/Services/Website/WebsiteServiceTest.php',
            $tests.'/Feature/Website/WebsiteCrudFlowTest.php',
            $tests.'/Feature/Api/V1/Website/ListWebsitesEndpointTest.php',
            $tests.'/Feature/Api/V1/Website/StoreWebsiteEndpointTest.php',
            $tests.'/Feature/Api/V1/Website/ShowWebsiteEndpointTest.php',
            $tests.'/Feature/Api/V1/Website/UpdateWebsiteEndpointTest.php',
            $tests.'/Feature/Api/V1/Website/DestroyWebsiteEndpointTest.php',
            $tests.'/Feature/Api/V1/Website/WebsiteAuthorizationEndpointTest.php',
            $tests.'/Contract/OpenApi/WebsiteOpenApiSpecTest.php',
            dirname(__DIR__, 4).'/docs/WIDGET_MARKETPLACE_ARCHITECTURE.md',
            $tests.'/Unit/Architecture/WidgetMarketplaceArchitectureDocTest.php',
            $tests.'/Contract/OpenApi/WidgetMarketplaceOpenApiSpecTest.php',
        ];

        foreach ($files as $file) {
            $this->assertFileExists($file);
        }
    }

    #[Test]
    public function tag_domain_files_exist(): void
    {
        $app = dirname(__DIR__, 3).'/app';
        $tests = dirname(__DIR__, 3).'/tests';

        $files = [
            $app.'/Models/Tag.php',
            $app.'/Repositories/Contracts/TagRepositoryInterface.php',
            $app.'/Repositories/Eloquent/EloquentTagRepository.php',
            'app/DTOs/Tag/TagDTO.php',
            $app.'/DTOs/Tag/CreateTagDTO.php',
            $app.'/DTOs/Tag/UpdateTagDTO.php',
            $app.'/Services/Tag/TagService.php',
            $app.'/Http/Controllers/Api/V1/Tag/IndexTagsController.php',
            $app.'/Http/Controllers/Api/V1/Tag/StoreTagController.php',
            $app.'/Http/Controllers/Api/V1/Tag/ShowTagController.php',
            $app.'/Http/Controllers/Api/V1/Tag/UpdateTagController.php',
            $app.'/Http/Controllers/Api/V1/Tag/DestroyTagController.php',
            $app.'/Http/Requests/Tag/ListTagsRequest.php',
            $app.'/Http/Requests/Tag/CreateTagRequest.php',
            $app.'/Http/Requests/Tag/ShowTagRequest.php',
            $app.'/Http/Requests/Tag/UpdateTagRequest.php',
            $app.'/Http/Requests/Tag/DestroyTagRequest.php',
            dirname(__DIR__, 3).'/database/factories/TagFactory.php',
            dirname(__DIR__, 3).'/database/migrations/2026_06_08_170000_create_tags_table.php',
            dirname(__DIR__, 3).'/database/migrations/2026_06_08_170001_create_website_tags_table.php',
            $tests.'/Unit/Database/TagsMigrationTest.php',
            $tests.'/Feature/Models/TagModelTest.php',
            $tests.'/Unit/Repositories/Eloquent/EloquentTagRepositoryTest.php',
            $tests.'/Unit/DTOs/Tag/TagDTOTest.php',
            $tests.'/Unit/DTOs/Tag/CreateTagDTOTest.php',
            $tests.'/Unit/DTOs/Tag/UpdateTagDTOTest.php',
            $tests.'/Unit/Services/Tag/TagServiceTest.php',
            $tests.'/Feature/Tag/TagCrudFlowTest.php',
            $tests.'/Feature/Api/V1/Tag/ListTagsEndpointTest.php',
            $tests.'/Feature/Api/V1/Tag/StoreTagEndpointTest.php',
            $tests.'/Feature/Api/V1/Tag/ShowTagEndpointTest.php',
            $tests.'/Feature/Api/V1/Tag/UpdateTagEndpointTest.php',
            $tests.'/Feature/Api/V1/Tag/DestroyTagEndpointTest.php',
            $tests.'/Feature/Api/V1/Tag/TagAuthorizationEndpointTest.php',
            $tests.'/Concerns/InteractsWithTags.php',
            $tests.'/Contract/OpenApi/TagOpenApiSpecTest.php',
        ];

        foreach ($files as $file) {
            $this->assertFileExists($file);
        }
    }

    #[Test]
    public function website_tag_domain_files_exist(): void
    {
        $app = dirname(__DIR__, 3).'/app';
        $tests = dirname(__DIR__, 3).'/tests';

        $files = [
            $app.'/Models/WebsiteTag.php',
            $app.'/Repositories/Contracts/WebsiteTagRepositoryInterface.php',
            $app.'/Repositories/Eloquent/EloquentWebsiteTagRepository.php',
            $app.'/DTOs/Website/WebsiteTagsDTO.php',
            $app.'/DTOs/Website/SyncWebsiteTagsDTO.php',
            $app.'/Services/Website/WebsiteTagService.php',
            dirname(__DIR__, 3).'/database/migrations/2026_06_08_170001_create_website_tags_table.php',
            $tests.'/Unit/Database/WebsiteTagsMigrationTest.php',
            $tests.'/Feature/Models/WebsiteTagRelationshipTest.php',
            $tests.'/Unit/Repositories/Eloquent/EloquentWebsiteTagRepositoryTest.php',
            $tests.'/Unit/Services/Website/WebsiteTagServiceTest.php',
            $tests.'/Unit/DTOs/Website/WebsiteTagsDTOTest.php',
            $tests.'/Contract/OpenApi/WebsiteTagOpenApiSpecTest.php',
        ];

        foreach ($files as $file) {
            $this->assertFileExists($file);
        }
    }

    #[Test]
    public function widget_domain_files_exist(): void
    {
        $app = dirname(__DIR__, 3).'/app';
        $tests = dirname(__DIR__, 3).'/tests';

        $files = [
            $app.'/Enums/WidgetStatus.php',
            $app.'/Models/Widget.php',
            $app.'/Repositories/Contracts/WidgetRepositoryInterface.php',
            $app.'/Repositories/Eloquent/EloquentWidgetRepository.php',
            $app.'/DTOs/Widget/WidgetDTO.php',
            $app.'/DTOs/Widget/ListWidgetCatalogQueryDTO.php',
            $app.'/DTOs/Widget/RegisterWidgetDTO.php',
            $app.'/Services/Widget/WidgetCatalogService.php',
            $app.'/Services/Widget/WidgetService.php',
            $app.'/Http/Controllers/Api/V1/Widget/IndexWidgetsController.php',
            $app.'/Http/Controllers/Api/V1/Widget/RegisterWidgetController.php',
            $app.'/Http/Controllers/Api/V1/Widget/StoreWebsiteWidgetController.php',
            $app.'/Http/Controllers/Api/V1/Widget/ActivateWidgetController.php',
            $app.'/Http/Controllers/Api/V1/Widget/DeactivateWidgetController.php',
            $app.'/Http/Requests/Widget/RegisterWidgetRequest.php',
            $app.'/Http/Requests/Widget/InstallWidgetRequest.php',
            $app.'/Http/Requests/Widget/ListWidgetsRequest.php',
            $app.'/Http/Requests/Widget/ActivateWidgetRequest.php',
            $app.'/Http/Requests/Widget/DeactivateWidgetRequest.php',
            dirname(__DIR__, 3).'/database/factories/WidgetFactory.php',
            dirname(__DIR__, 3).'/database/migrations/2026_06_09_180000_create_widgets_table.php',
            $tests.'/Unit/Database/WidgetsMigrationTest.php',
            $tests.'/Unit/Enums/WidgetStatusTest.php',
            $tests.'/Feature/Models/WidgetModelTest.php',
            $tests.'/Unit/Repositories/Eloquent/EloquentWidgetRepositoryTest.php',
            $tests.'/Unit/DTOs/Widget/WidgetDTOTest.php',
            $tests.'/Unit/DTOs/Widget/ListWidgetCatalogQueryDTOTest.php',
            $tests.'/Unit/DTOs/Widget/RegisterWidgetDTOTest.php',
            $tests.'/Unit/Services/Widget/WidgetCatalogServiceTest.php',
            $tests.'/Unit/Services/Widget/WidgetServiceTest.php',
            $tests.'/Feature/Widget/WidgetRegistrationFlowTest.php',
            $tests.'/Feature/Widget/WidgetActivationFlowTest.php',
            $tests.'/Feature/Widget/WidgetVersionPublishingFlowTest.php',
            $tests.'/Feature/Widget/WidgetVersionRollbackFlowTest.php',
            $tests.'/Feature/Api/V1/Widget/WidgetAuthorizationEndpointTest.php',
            $tests.'/Feature/Api/V1/Widget/ListWidgetsEndpointTest.php',
            $tests.'/Feature/Api/V1/Widget/InstallWebsiteWidgetEndpointTest.php',
            $tests.'/Feature/Api/V1/Widget/WidgetVersionAuthorizationEndpointTest.php',
            $tests.'/Concerns/InteractsWithWidgets.php',
            $app.'/Enums/WidgetVersionStatus.php',
            $app.'/Models/WidgetVersion.php',
            $app.'/Repositories/Contracts/WidgetVersionRepositoryInterface.php',
            $app.'/Repositories/Eloquent/EloquentWidgetVersionRepository.php',
            $app.'/DTOs/Widget/WidgetVersionDTO.php',
            $app.'/Services/Widget/WidgetVersionService.php',
            $app.'/Http/Controllers/Api/V1/Widget/PublishWidgetVersionController.php',
            $app.'/Http/Controllers/Api/V1/Widget/DeprecateWidgetVersionController.php',
            $app.'/Http/Controllers/Api/V1/Widget/RollbackWidgetVersionController.php',
            $app.'/Http/Requests/Widget/PublishWidgetVersionRequest.php',
            $app.'/Http/Requests/Widget/DeprecateWidgetVersionRequest.php',
            $app.'/Http/Requests/Widget/RollbackWidgetVersionRequest.php',
            dirname(__DIR__, 3).'/database/factories/WidgetVersionFactory.php',
            dirname(__DIR__, 3).'/database/migrations/2026_06_09_180001_create_widget_versions_table.php',
            $tests.'/Unit/Database/WidgetVersionsMigrationTest.php',
            $tests.'/Unit/Enums/WidgetVersionStatusTest.php',
            $tests.'/Feature/Models/WidgetVersionModelTest.php',
            $tests.'/Unit/Repositories/Eloquent/EloquentWidgetVersionRepositoryTest.php',
            $tests.'/Unit/DTOs/Widget/WidgetVersionDTOTest.php',
            $tests.'/Unit/Services/Widget/WidgetVersionServiceTest.php',
            $app.'/Models/WidgetCategory.php',
            $app.'/Models/WidgetCategoryWidget.php',
            $app.'/Repositories/Contracts/WidgetCategoryRepositoryInterface.php',
            $app.'/Repositories/Contracts/WidgetCategoryWidgetRepositoryInterface.php',
            $app.'/Repositories/Eloquent/EloquentWidgetCategoryRepository.php',
            $app.'/Repositories/Eloquent/EloquentWidgetCategoryWidgetRepository.php',
            $app.'/DTOs/Widget/WidgetCategoryDTO.php',
            $app.'/DTOs/Widget/WidgetCategoriesDTO.php',
            $app.'/DTOs/Widget/SyncWidgetCategoriesDTO.php',
            $app.'/Services/Widget/WidgetCategoryService.php',
            dirname(__DIR__, 3).'/database/factories/WidgetCategoryFactory.php',
            dirname(__DIR__, 3).'/database/migrations/2026_06_09_180002_create_widget_categories_table.php',
            dirname(__DIR__, 3).'/database/migrations/2026_06_09_180003_create_widget_category_widget_table.php',
            $tests.'/Unit/Database/WidgetCategoriesMigrationTest.php',
            $tests.'/Unit/Database/WidgetCategoryWidgetMigrationTest.php',
            $tests.'/Feature/Models/WidgetCategoryModelTest.php',
            $tests.'/Unit/Repositories/Eloquent/EloquentWidgetCategoryRepositoryTest.php',
            $tests.'/Unit/DTOs/Widget/WidgetCategoryDTOTest.php',
            $tests.'/Unit/DTOs/Widget/WidgetCategoriesDTOTest.php',
            $tests.'/Unit/DTOs/Widget/SyncWidgetCategoriesDTOTest.php',
            $tests.'/Unit/Services/Widget/WidgetCategoryServiceTest.php',
            $tests.'/Unit/Repositories/Eloquent/EloquentWidgetCategoryWidgetRepositoryTest.php',
            $app.'/Models/WidgetTemplate.php',
            $app.'/Repositories/Contracts/WidgetTemplateRepositoryInterface.php',
            $app.'/Repositories/Eloquent/EloquentWidgetTemplateRepository.php',
            $app.'/DTOs/Widget/WidgetTemplateDTO.php',
            $app.'/Services/Widget/WidgetTemplateService.php',
            dirname(__DIR__, 3).'/database/factories/WidgetTemplateFactory.php',
            dirname(__DIR__, 3).'/database/migrations/2026_06_09_180004_create_widget_templates_table.php',
            $tests.'/Unit/Database/WidgetTemplatesMigrationTest.php',
            $tests.'/Feature/Models/WidgetTemplateModelTest.php',
            $tests.'/Unit/Repositories/Eloquent/EloquentWidgetTemplateRepositoryTest.php',
            $tests.'/Unit/DTOs/Widget/WidgetTemplateDTOTest.php',
            $tests.'/Unit/Services/Widget/WidgetTemplateServiceTest.php',
            $app.'/DTOs/Widget/WidgetTemplatesDTO.php',
            $app.'/DTOs/Widget/AssignWidgetTemplateDTO.php',
            $app.'/Services/Widget/WidgetTemplateAssignmentService.php',
            $tests.'/Unit/DTOs/Widget/WidgetTemplatesDTOTest.php',
            $tests.'/Unit/DTOs/Widget/AssignWidgetTemplateDTOTest.php',
            $tests.'/Unit/Services/Widget/WidgetTemplateAssignmentServiceTest.php',
            $app.'/Enums/WebsiteWidgetStatus.php',
            $app.'/Models/WebsiteWidget.php',
            $app.'/Repositories/Contracts/WebsiteWidgetRepositoryInterface.php',
            $app.'/Repositories/Eloquent/EloquentWebsiteWidgetRepository.php',
            $app.'/DTOs/Widget/WebsiteWidgetDTO.php',
            $app.'/DTOs/Widget/InstallWidgetDTO.php',
            $app.'/DTOs/Widget/UpdateWebsiteWidgetDTO.php',
            $app.'/Services/Widget/WebsiteWidgetService.php',
            dirname(__DIR__, 3).'/database/factories/WebsiteWidgetFactory.php',
            dirname(__DIR__, 3).'/database/migrations/2026_06_09_180005_create_website_widgets_table.php',
            $tests.'/Unit/Database/WebsiteWidgetsMigrationTest.php',
            $tests.'/Unit/Enums/WebsiteWidgetStatusTest.php',
            $tests.'/Feature/Models/WebsiteWidgetModelTest.php',
            $tests.'/Unit/Repositories/Eloquent/EloquentWebsiteWidgetRepositoryTest.php',
            $tests.'/Unit/DTOs/Widget/WebsiteWidgetDTOTest.php',
            $tests.'/Unit/DTOs/Widget/InstallWidgetDTOTest.php',
            $tests.'/Unit/DTOs/Widget/UpdateWebsiteWidgetDTOTest.php',
            $tests.'/Unit/Services/Widget/WebsiteWidgetServiceTest.php',
        ];

        foreach ($files as $file) {
            $this->assertFileExists($file);
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
            'Feature/Auth/AuthorizationGateTest.php',
            'Feature/Auth/EnsurePermissionMiddlewareTest.php',
            'Unit/Http/Middleware/EnsurePermissionTest.php',
            'Unit/Services/Auth/AuthorizationServiceTest.php',
            'Unit/Services/Auth/PermissionServiceTest.php',
            'Unit/Repositories/Permissions/ConfigPermissionsRepositoryTest.php',
            'Unit/DTOs/Auth/UserPermissionsDTOTest.php',
        ];

        foreach ($files as $file) {
            $this->assertFileExists($base.'/'.$file);
        }
    }
}

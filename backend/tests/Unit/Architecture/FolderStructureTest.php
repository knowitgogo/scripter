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
            'app/Events',
            'app/Listeners',
            'app/Jobs',
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
            'tests/Unit',
            'tests/Feature',
            'tests/Contract',
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
            'Support/ApiResponse.php',
            'Support/ApiExceptionRenderer.php',
            'Support/UuidGenerator.php',
            'Http/Middleware/ForceJsonResponse.php',
            'Repositories/Contracts/RepositoryInterface.php',
            'Repositories/Eloquent/EloquentRepository.php',
            'Http/Requests/ApiRequest.php',
            'Models/Concerns/HasUuid.php',
            'Exceptions/DomainException.php',
            'Providers/RepositoryServiceProvider.php',
        ];

        foreach ($files as $file) {
            $this->assertFileExists($base.'/'.$file);
        }
    }
}

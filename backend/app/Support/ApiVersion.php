<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Central API version registry and helpers.
 */
final class ApiVersion
{
    public static function default(): string
    {
        return (string) config('api.default_version', 'v1');
    }

    /**
     * @return list<string>
     */
    public static function supported(): array
    {
        /** @var list<string> $versions */
        $versions = config('api.supported_versions', ['v1']);

        return $versions;
    }

    public static function isSupported(string $version): bool
    {
        return in_array($version, self::supported(), true);
    }

    public static function fromRequest(Request $request): string
    {
        $version = $request->attributes->get('api_version');

        if (is_string($version) && self::isSupported($version)) {
            return $version;
        }

        return self::default();
    }

    public static function routePrefix(?string $version = null): string
    {
        $version ??= self::default();

        return rtrim((string) config('api.prefix', 'api'), '/').'/'.$version;
    }

    public static function versionHeader(): string
    {
        return (string) config('api.version_header', 'X-API-Version');
    }
}

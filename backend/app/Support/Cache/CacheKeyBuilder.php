<?php

declare(strict_types=1);

namespace App\Support\Cache;

/**
 * Builds cache keys from configured patterns and UUID parameters.
 */
final class CacheKeyBuilder
{
    /**
     * @param  array<string, string>  $params
     */
    public static function build(string $pattern, array $params): string
    {
        $search = [];
        $replace = [];

        foreach ($params as $key => $value) {
            $search[] = '{'.$key.'}';
            $replace[] = $value;
        }

        return str_replace($search, $replace, $pattern);
    }

    public static function pattern(string $patternKey): string
    {
        return (string) config("infrastructure.cache.patterns.{$patternKey}");
    }

    public static function ttl(string $patternKey): int
    {
        return (int) config("infrastructure.cache.ttl.{$patternKey}");
    }
}

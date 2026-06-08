<?php

declare(strict_types=1);

namespace App\Services\Infrastructure;

use App\Repositories\Contracts\CacheRepositoryInterface;
use App\Support\Cache\CacheKeyBuilder;

/**
 * Service-level cache-aside using configured key patterns and TTLs.
 */
final class CacheService
{
    public function __construct(
        private readonly CacheRepositoryInterface $cache,
    ) {}

    /**
     * @param  array<string, string>  $params
     */
    public function remember(string $patternKey, array $params, callable $callback): mixed
    {
        $key = CacheKeyBuilder::build(CacheKeyBuilder::pattern($patternKey), $params);

        return $this->cache->remember(
            $key,
            CacheKeyBuilder::ttl($patternKey),
            $callback,
        );
    }

    /**
     * @param  array<string, string>  $params
     */
    public function forget(string $patternKey, array $params): bool
    {
        $key = CacheKeyBuilder::build(CacheKeyBuilder::pattern($patternKey), $params);

        return $this->cache->forget($key);
    }

    /**
     * @param  array<string, string>  $params
     */
    public function get(string $patternKey, array $params, mixed $default = null): mixed
    {
        $key = CacheKeyBuilder::build(CacheKeyBuilder::pattern($patternKey), $params);

        return $this->cache->get($key, $default);
    }

    /**
     * @param  array<string, string>  $params
     */
    public function put(string $patternKey, array $params, mixed $value): bool
    {
        $key = CacheKeyBuilder::build(CacheKeyBuilder::pattern($patternKey), $params);

        return $this->cache->put($key, $value, CacheKeyBuilder::ttl($patternKey));
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Cache;

use App\Repositories\Contracts\CacheRepositoryInterface;
use Illuminate\Contracts\Cache\Repository as CacheStore;
use Illuminate\Support\Facades\Cache;

/**
 * Laravel cache store adapter.
 */
final class LaravelCacheRepository implements CacheRepositoryInterface
{
    public function get(string $key, mixed $default = null, ?string $store = null): mixed
    {
        return $this->store($store)->get($key, $default);
    }

    public function put(string $key, mixed $value, int $ttl, ?string $store = null): bool
    {
        return $this->store($store)->put($key, $value, $ttl);
    }

    public function remember(string $key, int $ttl, callable $callback, ?string $store = null): mixed
    {
        return $this->store($store)->remember($key, $ttl, $callback);
    }

    public function forget(string $key, ?string $store = null): bool
    {
        return $this->store($store)->forget($key);
    }

    public function has(string $key, ?string $store = null): bool
    {
        return $this->store($store)->has($key);
    }

    private function store(?string $store): CacheStore
    {
        return Cache::store($store ?? (string) config('infrastructure.cache.store'));
    }
}

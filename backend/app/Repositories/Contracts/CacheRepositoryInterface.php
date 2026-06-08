<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

/**
 * Cache storage abstraction. Services use this instead of the Cache facade.
 */
interface CacheRepositoryInterface extends RepositoryInterface
{
    public function get(string $key, mixed $default = null, ?string $store = null): mixed;

    public function put(string $key, mixed $value, int $ttl, ?string $store = null): bool;

    public function remember(string $key, int $ttl, callable $callback, ?string $store = null): mixed;

    public function forget(string $key, ?string $store = null): bool;

    public function has(string $key, ?string $store = null): bool;
}

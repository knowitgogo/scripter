<?php

declare(strict_types=1);

namespace App\Repositories\Infrastructure;

use App\Repositories\Contracts\InfrastructureProbeRepositoryInterface;
use App\Support\UuidGenerator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * Checks connectivity to infrastructure dependencies.
 */
final class InfrastructureProbeRepository implements InfrastructureProbeRepositoryInterface
{
    public function isDatabaseReachable(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function isCacheReachable(): bool
    {
        try {
            $key = 'readiness:probe:'.UuidGenerator::generate();

            Cache::put($key, true, 10);

            return Cache::get($key) === true;
        } catch (Throwable) {
            return false;
        }
    }

    public function isRedisReachable(): bool
    {
        if (! config('infrastructure.redis.enabled')) {
            return true;
        }

        try {
            $response = Redis::connection('default')->ping();

            if ($response === true) {
                return true;
            }

            return is_string($response) && strtoupper($response) === 'PONG';
        } catch (Throwable) {
            return false;
        }
    }
}

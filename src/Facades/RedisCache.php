<?php

namespace Chantouch\JWTRedis\Facades;

use Illuminate\Support\Facades\Facade;
use Chantouch\JWTRedis\Contracts\RedisCacheContract;

/**
 * Class RedisCache.
 */
class RedisCache extends Facade
{
    /**
     * @return string
     */
    public static function getFacadeAccessor(): string
    {
        return RedisCacheContract::class;
    }
}

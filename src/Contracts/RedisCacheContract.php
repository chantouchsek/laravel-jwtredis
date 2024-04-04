<?php

namespace Chantouch\JWTRedis\Contracts;

/**
 * Interface RedisCacheContract
 *
 * This interface defines the contract for a Redis cache implementation.
 * It includes methods for setting the key and data, removing, getting, refreshing, and caching data.
 */
interface RedisCacheContract
{
    /**
     * Set the key for the cache.
     *
     * @param string $key The key to be used for the cache.
     *
     * @return RedisCacheContract Returns the instance of the class implementing this interface.
     */
    public function key(string $key): self;

    /**
     * Set the data for the cache.
     *
     * @param mixed $data The data to be cached.
     *
     * @return RedisCacheContract Returns the instance of the class implementing this interface.
     */
    public function data($data): self;

    /**
     * Remove the cache.
     *
     * @return mixed Returns the result of the cache removal operation.
     */
    public function removeCache(): mixed;

    /**
     * Get the cache.
     *
     * @return mixed Returns the cached data.
     */
    public function getCache(): mixed;

    /**
     * Refresh the cache.
     *
     * @return mixed Returns the result of the cache refresh operation.
     */
    public function refreshCache(): mixed;

    /**
     * Cache the data.
     *
     * @return mixed Returns the result of the cache operation.
     */
    public function cache(): mixed;
}
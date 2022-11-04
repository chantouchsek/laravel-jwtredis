<?php

namespace Chantouch\JWTRedis\Contracts;

/**
 * Interface RedisCacheContract.
 */
interface RedisCacheContract
{
    /**
     * @param string $key
     *
     * @return RedisCacheContract
     */
    public function key(string $key): self;

    /**
     * @param $data
     *
     * @return RedisCacheContract
     */
    public function data($data): self;

    /**
     * @return mixed
     */
    public function removeCache(): mixed;

    /**
     * @return mixed
     */
    public function getCache(): mixed;

    /**
     * @return mixed
     */
    public function refreshCache(): mixed;

    /**
     * @return mixed
     */
    public function cache(): mixed;
}

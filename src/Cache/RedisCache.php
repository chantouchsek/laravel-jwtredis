<?php

namespace Chantouch\JWTRedis\Cache;

use Illuminate\Support\Facades\Cache;
use Chantouch\JWTRedis\Contracts\RedisCacheContract;

class RedisCache implements RedisCacheContract
{
    /** @var mixed */
    protected mixed $data;

    /** @var int */
    private int $time;

    /** @var string */
    protected string $key;

    /**
     * @param string $key
     *
     * @return RedisCacheContract
     */
    public function key(string $key): RedisCacheContract
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @param $data
     *
     * @return RedisCacheContract
     */
    public function data($data): RedisCacheContract
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCache(): mixed
    {
        return Cache::get($this->key);
    }

    /**
     * @return bool
     */
    public function removeCache(): bool
    {
        return Cache::forget($this->key);
    }

    /**
     * @return bool|mixed
     */
    public function refreshCache(): mixed
    {
        if (!$this->getCache()) {
            return false;
        }

        $this->key($this->key)->removeCache();

        return $this->key($this->key)->data($this->data)->cache();
    }

    /**
     * @return mixed
     */
    public function cache(): mixed
    {
        $this->setTime();

        return Cache::remember($this->key, $this->time, function () {
            return $this->data;
        });
    }

    /**
     * @return void
     */
    private function setTime(): void
    {
        $this->time = (config('jwt-redis.redis_ttl_jwt') ? config('jwt.ttl') : config('jwt-redis.redis_ttl')) * 60;
    }
}

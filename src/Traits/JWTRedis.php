<?php

namespace Chantouch\JWTRedis\Traits;

use Illuminate\Database\Eloquent\Model;

trait JWTRedis
{
    /**
     * @return bool
     */
    public function checkUserStatus(): bool
    {
        $column = config('jwt-redis.status_column_title');
        $values = config('jwt-redis.banned_statuses');

        return !in_array($this->$column, $values);
    }

    /**
     * Get the stored key in the Redis for user data.
     *
     * @return string
     */
    public function getRedisKey(): string
    {
        return config('jwt-redis.redis_auth_prefix').$this->getJWTIdentifier();
    }

    /**
     * @return void
     */
    public function triggerTheObserver()
    {
        /** @var Model $model */
        $model = $this;

        $class = config('jwt-redis.observer');

        (new $class())->updated($model);
    }
}

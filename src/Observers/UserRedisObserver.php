<?php

namespace Chantouch\JWTRedis\Observers;

use Illuminate\Database\Eloquent\Model;
use Chantouch\JWTRedis\Facades\RedisCache;
use Chantouch\JWTRedis\Jobs\ProcessObserver;

/**
 * Class UserRedisObserver.
 */
class UserRedisObserver
{
    /**
     * Handle the Model "updated" event.
     *
     * @param Model $model
     * @return void|string
     */
    public function updated(Model $model)
    {
        if (config('jwt-redis.observer_events_queue')) {
            dispatch((new ProcessObserver($model, __FUNCTION__)));
        } else {
            // Refresh user.
            $model = config('jwt-redis.user_model')::find($model->id);

            return RedisCache::key($model->getRedisKey())
                ->data($model->load(config('jwt-redis.cache_relations')))
                ->refreshCache();
        }
    }

    /**
     * Handle the Model "deleted" event.
     *
     * @param Model $model
     * @return void|string
     */
    public function deleted(Model $model)
    {
        if (config('jwt-redis.observer_events_queue')) {
            dispatch((new ProcessObserver($model, __FUNCTION__)));
        } else {
            return RedisCache::key($model->getRedisKey())->removeCache();
        }
    }
}

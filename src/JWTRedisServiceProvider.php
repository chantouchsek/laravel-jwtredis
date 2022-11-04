<?php

namespace Chantouch\JWTRedis;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Chantouch\JWTRedis\Cache\RedisCache;
use Chantouch\JWTRedis\Contracts\RedisCacheContract;
use Chantouch\JWTRedis\Guards\JWTRedisGuard;
use Chantouch\JWTRedis\Providers\JWTRedisUserProvider;

class JWTRedisServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->bindRedisCache();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfig();
        $this->overrideJWTGuard();
        $this->overrideUserProvider();
        $this->bindObservers();
    }

    protected function publishConfig()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/jwt-redis.php', 'jwt-redis');

        $this->publishes([__DIR__ . '/config/jwt-redis.php' => config_path('jwt-redis.php')], 'config');
    }

    protected function overrideJWTGuard()
    {
        // Override JWT Guard for without DB query.
        Auth::extend('jwt_redis', function ($app, $name, array $config) {

            // Return an instance of Illuminate\Contracts\Auth\Guard...
            return new JWTRedisGuard($app['tymon.jwt'], Auth::createUserProvider($config['provider']), $app['request'], $app['events']);
        });
    }

    protected function overrideUserProvider()
    {
        /**
         * Override Eloquent Provider for fetching user with role&permission query.
         */
        Auth::provider('jwt_redis_user', function ($app, array $config) {

            // Return an instance of Illuminate\Contracts\Auth\UserProviderContract...
            return new JWTRedisUserProvider($app['hash'], $config['model']);
        });
    }

    protected function bindRedisCache()
    {
        $this->app->bind(RedisCacheContract::class, function ($app) {
            return new RedisCache();
        });
    }

    protected function bindObservers()
    {
        if (class_exists(config('jwt-redis.user_model'))) {
            config('jwt-redis.user_model')::observe(config('jwt-redis.observer'));
        }
    }
}

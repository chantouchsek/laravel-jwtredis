<?php

namespace Chantouch\JWTRedis\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JWTRedisUserProvider extends EloquentUserProvider implements UserProviderContract
{
    /**
     * @OVERRIDE!
     *
     * Retrieve a user by the given credentials.
     *
     * !Important; I made some changes to this method for eager loading user roles&permissions.
     *
     * @param array $credentials
     *
     * @return Builder|Model|object|void
     */
    public function retrieveByCredentials(array $credentials)
    {
        $credentials = array_filter($credentials, fn ($key) => ! str_contains($key, 'password'), ARRAY_FILTER_USE_KEY);

        if (empty($credentials)) {
            return;
        }

        // First, we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we find a user, return it in an
        // Eloquent User "model" that will be used by the Guard instances.
        $query = $this->newModelQuery()->with(config('jwt-redis.cache_relations'));

        foreach ($credentials as $key => $value) {
            if (Str::contains($key, 'password')) {
                continue;
            }

            if (is_array($value) || $value instanceof Arrayable) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }
}

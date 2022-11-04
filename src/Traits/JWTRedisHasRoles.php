<?php

namespace Chantouch\JWTRedis\Traits;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles as OriginalHasRole;
use Spatie\Permission\Traits\HasPermissions as OriginalHasPermission;

trait JWTRedisHasRoles
{
    use OriginalHasRole {
        assignRole as protected originalAssignRole;
        givePermissionTo as protected originalGivePermissionTo;
    }
    use OriginalHasPermission {
        hasDirectPermission as protected originalHasDirectPermission;
    }

    /**
     * @OVERRIDE!
     *
     * !Important; Triggering the observer.
     *
     * @param mixed ...$roles
     *
     * @return $this
     */
    public function assignRole(...$roles): static
    {
        $this->originalAssignRole(...$roles);

        $this->triggerTheObserver();

        return $this;
    }

    /**
     * @OVERRIDE!
     *
     * !Important; Made only one changes this method for triggering observer.
     *
     * @param mixed ...$permissions
     *
     * @return $this
     */
    public function givePermissionTo(...$permissions): static
    {
        $this->originalGivePermissionTo(...$permissions);

        $this->triggerTheObserver();

        return $this;
    }

    /**
     * @OVERRIDE!
     *
     * !Important; Made only one changes this method for triggering observer.
     *
     * @param $permission
     * @param null $guardName
     * @return bool
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        return $this->hasDirectPermission($permission) || $this->hasPermissionViaRole($permission);
    }

    /**
     * @OVERRIDE!
     *
     * !Important; Made some changes this method for check authed user only permissions without db query.
     *
     * @param $permission
     * @return bool
     */
    public function hasDirectPermission($permission): bool
    {
        if (is_string($permission)) {
            return $this->permissions->contains('name', $permission);
        }

        if (is_int($permission)) {
            return $this->permissions->contains('id', $permission);
        }

        return $this->originalHasDirectPermission($permission);
    }

    /**
     * @OVERRIDE!
     *
     * !Important; Made some changes this method for check authed user only permissions of role without db query.
     *
     * @param $permission
     * @return bool
     */
    public function hasPermissionViaRole($permission): bool
    {
        $roles = $this->roles;

        if (is_string($permission)) {
            foreach ($roles as $role) {
                if ($role->permissions->contains('name', $permission)) {
                    return true;
                }
            }
        }

        if (is_int($permission)) {
            foreach ($roles as $role) {
                if ($role->permissions->contains('id', $permission)) {
                    return true;
                }
            }
        }

        return false;
    }

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
    public function triggerTheObserver(): void
    {
        /** @var Model $model */
        $model = $this;

        $class = config('jwt-redis.observer');

        (new $class())->updated($model);
    }
}

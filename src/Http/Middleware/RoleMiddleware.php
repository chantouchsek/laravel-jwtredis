<?php

namespace Chantouch\JWTRedis\Http\Middleware;

use Closure;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class RoleMiddleware extends BaseMiddleware
{
    /**
     * @param $request
     * @param Closure $next
     * @param $role
     *
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        try {
            $this->setIfClaimIsNotExist($request);
        } catch (TokenExpiredException | TokenInvalidException | JWTException $e) {
            return $this->respondWithError($e, 401);
        }

        $this->setAuthedUser($request);

        $roles = is_array($role) ? $role : explode('|', $role);

        if (config('jwt-redis.check_banned_user')) {
            if (!$request->authedUser->checkUserStatus()) {
                return $this->respondWithError('AccountBlockedException', 401);
            }
        }

        if (!$request->authedUser->hasAnyRole($roles)) {
            return $this->respondWithError('RoleException', 401);
        }

        return $next($request);
    }
}

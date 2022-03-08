<?php

namespace Chantouch\JWTRedis\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class RoleOrPermissionMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param $request
     * @param Closure $next
     * @param $roleOrPermission
     *
     * @return JsonResponse|mixed
     */
    public function handle($request, Closure $next, $roleOrPermission)
    {
        try {
            $this->setIfClaimIsNotExist($request);
        } catch (TokenExpiredException | TokenInvalidException | JWTException $e) {
            return $this->respondWithError($e, 401);
        }

        $this->setAuthedUser($request);

        $rolesOrPermissions = is_array($roleOrPermission) ? $roleOrPermission : explode('|', $roleOrPermission);

        if (!$request->authedUser->hasAnyRole($rolesOrPermissions) && !$request->authedUser->hasAnyPermission($rolesOrPermissions)) {
            return $this->respondWithError('RoleOrPermissionException', 403);
        }

        return $next($request);
    }
}

<?php

namespace Chantouch\JWTRedis\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Throwable;

class Authenticate extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param $request
     * @param Closure $next
     *
     * @return JsonResponse
     * @throws Throwable
     */
    public function handle($request, Closure $next): JsonResponse
    {
        try {
            $this->setIfClaimIsNotExist($request);
        } catch (Throwable $e) {
            return $this->respondWithError($e, 401);
        }

        return $next($request);
    }
}

<?php

namespace Chantouch\JWTRedis\Http\Middleware;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Token;

abstract class BaseMiddleware
{
    /**
     *  If you don't use Authentication Middleware before that Middleware,
     *  application need to set a Claim (by Token) in Request object for
     *  using Laravel's Auth facade.
     *
     * @param $request
     *
     * @return bool
     */
    protected function setIfClaimIsNotExist($request): bool
    {
        if ($request->claim === null) {
            /** @var Token $token */
            $token = JWTAuth::getPayload(JWTAuth::getToken());

            /** Application need this assignment for using Laravel's Auth facade. */
            $request->claim = $token->get('sub');
        }

        return true;
    }

    /**
     * This first request always comes from Redis,
     * then will always be stored in this Request object.
     *
     * @param $request
     */
    protected function setAuthedUser($request)
    {
        $request->authedUser = Auth::user();
    }


    /**
     * Will return a response.
     *
     * @param array $data The given data
     * @param int $statusCode
     * @param array $headers The given headers
     *
     * @return JsonResponse The JSON-response
     */
    public function respond($data, $statusCode, $headers = []): JsonResponse
    {
        return Response::json($data, $statusCode, $headers);
    }

    /**
     * @param $exception
     * @param int $statusCode
     * @param array $headers
     * @return JsonResponse
     */
    public function respondWithError($exception, $statusCode, $headers = []): JsonResponse
    {
        $error = config('jwt-redis.errors.' . class_basename($exception)) ?? config('jwt-redis.errors.default');

        return $this->respond([
            'message' => $error['message'],
            'status_code' => $error['code'] ?? $statusCode,
        ], $statusCode, $headers);
    }
}

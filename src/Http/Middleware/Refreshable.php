<?php

namespace Chantouch\JWTRedis\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Manager;
use PHPOpenSourceSaver\JWTAuth\Token;

class Refreshable extends BaseMiddleware
{
    /**
     * The JWT Authenticator.
     *
     * @var JWTAuth
     */
    protected JWTAuth $auth;

    /**
     * @var Manager
     */
    protected Manager $manager;

    /**
     * @param JWTAuth $auth
     *
     * @param Manager $manager
     */
    public function __construct(JWTAuth $auth, Manager $manager)
    {
        $this->auth = $auth;
        $this->manager = $manager;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return JsonResponse
     *@throws UnauthorizedHttpException
     *
     */
    public function handle($request, Closure $next)
    {
        $this->checkForToken($request);

        try {
            $token = $this->auth->parseToken()->refresh();

            /** Application needs this assignment for using Laravel's Auth facade. */
            $request->claim = $this->manager->decode(new Token($token))->get('sub');
        } catch (TokenInvalidException | JWTException $e) {
            return $this->respondWithError($e, 401);
        }

        // Send the refreshed token back to the client.
        return $this->setAuthenticationResponse($token);
    }

    /**
     * Check the request for the presence of a token.
     *
     * @param Request $request
     *
     * @return JsonResponse|void
     */
    protected function checkForToken(Request $request)
    {
        if (!$this->auth->parser()->setRequest($request)->hasToken()) {
            return $this->respondWithError('TokenNotProvided', 403);
        }
    }

    /**
     * Set the token response.
     *
     * @param null $token
     * @return JsonResponse
     */
    protected function setAuthenticationResponse($token = null)
    {
        if (config('jwt-redis.check_banned_user')) {
            if (!Auth::user()->checkUserStatus()) {
                return $this->respondWithError('AccountBlockedException', 403);
            }
        }

        $token = $token ?: $this->auth->refresh();

        return $this->respond(['token' => $token], 200);
    }
}

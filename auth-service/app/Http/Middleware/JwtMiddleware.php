<?php

namespace App\Http\Middleware;

use App\Auth\JwtCachedGuard;
use App\Enums\AppAuthResponseCode;
use App\Exceptions\TokenBlacklistedException;
use App\Exceptions\JWTAuthException;
use App\Services\ExceptionJWTMiddlewareSafeDecodeService;
use App\Services\JwtRedisService;
use App\Services\UserCacheService;
use App\Traits\ApiResponseFormatter;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware {
    use ApiResponseFormatter;

    protected string|float $jwtAuthStart;

    public function __construct(
        protected JwtRedisService $jwtRedis,
        protected UserCacheService $userCache
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        $this->jwtAuthStart = microtime(true);

        try {
            //* Use custom JWT guard for authentication
            $guard = Auth::guard('api');

            /**
             * This will trigger the guard's user() method
             * dd($user, $guard, $request->user(), auth('api')->user(), $request->attributes, $guard->payload());
             * which handles:
             * - JWT token validation
             * - Banned token check
             * - Single sign-in verification (Disabled)
             * - User cache retrieval
             * - Request attributes storage
             */
            $user = $guard->user();
            if (!$user)
                throw new JWTAuthException(AppAuthResponseCode::Unauthorized);

            /*
            //*! Check 4: Rate Limiting (Unused - Rate limiter in API Gateway KONG)
            $rateLimit = $this->jwtRedis->checkRateLimit($userId, 100, 1);
            if (!$rateLimit['allowed'])
                throw new RateLimiterException();
            */

            /*
            //* Set authenticated user for this request
            $request->setUserResolver(function () use ($user) {
                // Auth::guard('api')->setUser($user);
                return $user;
            });
            */

            $this->stopProfiling($request);
        } catch (TokenBlacklistedException $e) {
            $this->handleProfiling($request, $guard);

            throw $e;
        } catch (JWTAuthException $e) {
            $this->handleProfiling($request, $guard);

            throw $e;
        }

        /*
        //*! Add rate limit headers (Unused - Rate limiter in API Gateway KONG)
        $response = $next($request);

        $response->headers->set('X-RateLimit-Limit', 100);
        $response->headers->set('X-RateLimit-Remaining', $rateLimit['remaining']);
        $response->headers->set('X-RateLimit-Reset', $rateLimit['reset_at']);

        return $response;
        */

        return $next($request);
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @param JwtCachedGuard $guard
     * @return void
     */
    protected function handleProfiling(Request $request, JwtCachedGuard $guard): void {
        /** @var \App\Auth\JwtCachedGuard $guard */
        $token = $guard->getTokenForRequest();

        $exceptionJWTMiddlewareSafeDecodeService = new ExceptionJWTMiddlewareSafeDecodeService();
        $payload = $exceptionJWTMiddlewareSafeDecodeService->safeDecode($token);
        if ($payload) {
            //* Set userId & roleId user for this request
            $request->attributes->set('userId', $payload['sub'] ?? null);
            $request->attributes->set('roleId', $payload['role'] ?? null);
        }

        $this->stopProfiling($request);
    }

    /**
     * Catat durasi auth saat sukses.
     */
    protected function stopProfiling(Request $request): void {
        $duration = round((microtime(true) - $this->jwtAuthStart) * 1000, 2);
        $request->attributes->set('stopProfilingMiddlewareJwtAuthTime', $duration);
    }
}

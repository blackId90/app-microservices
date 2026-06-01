<?php

namespace App\Http\Middleware;

use App\Enums\AppAuthResponseCode;
use App\Exceptions\JWTAuthException;
use App\Exceptions\TokenBlacklistedException;
use App\Traits\ApiResponseFormatter;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware {
    use ApiResponseFormatter;

    protected string|float $jwtAuthStart;

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
             * which handles:
             * - JWT token validation
             * - Banned token check
             * - Single sign-in verification (Disabled)
             * - User cache retrieval
             * - Request attributes storage
             *
             * dd($user, $guard, $request->user(), auth('api')->user(), $request->attributes, $user->payload());
             */
            $user = $guard->user();
            if (!$user)
                throw new JWTAuthException(AppAuthResponseCode::Unauthorized);

            /*
            //* Set authenticated user for this request (already done by guard, but for safety)
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
            */

            //* Profiling
            $this->stopProfiling($request);
        } catch (TokenBlacklistedException $e) {
            throw $e;
        } catch (JWTAuthException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new JWTAuthException(AppAuthResponseCode::InvalidToken);
        }

        return $next($request);
    }

    /**
     * Note the auth duration
     */
    protected function stopProfiling(Request $request): void {
        $duration = round((microtime(true) - $this->jwtAuthStart) * 1000, 2);
        $request->attributes->set('stopProfilingMiddlewareJwtAuthTime', $duration);
    }
}

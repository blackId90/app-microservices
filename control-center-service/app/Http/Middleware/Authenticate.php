<?php

namespace App\Http\Middleware;

use App\Enums\AppAuthResponseCode;
use App\Exceptions\JWTAuthException;
use App\Traits\ApiResponseFormatter;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware {
    use ApiResponseFormatter;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards) {
        try {
            // dd(Auth::guard('api')->user(), $request, $guards);
            $this->authenticate($request, $guards);
        } catch (AuthenticationException $e) {
            throw new JWTAuthException(AppAuthResponseCode::Unauthorized);
        }

        return $next($request);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string {
        //* For API, we don't redirect, we throw exception
        return null;
    }
}

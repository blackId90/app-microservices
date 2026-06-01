<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        $rawLocale = $request->header('Accept-Language', config('app.locale'));

        $locale = strtolower(substr($rawLocale, 0, 2));
        if (!in_array($locale, ['en', 'id']))
            $locale = config('app.locale');

        App::setLocale($locale);

        return $next($request);
    }
}

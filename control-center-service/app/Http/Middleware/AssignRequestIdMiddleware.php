<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponseFormatter;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Str;
// use Symfony\Component\HttpFoundation\Response;

class AssignRequestIdMiddleware {
    use ApiResponseFormatter;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next) {
        if (!$request->hasHeader('X-Profiling'))
            $request->attributes->set('profilingStart', microtime(true));

        $requestId = $request->header('X-Request-ID');
        $logId = $request->header('X-Log-ID');

        Log::withContext([
            'request-id' => $requestId,
            'log-id' => $logId
        ]);

        $request->attributes->set('requestTimestamp', now()->toISOString());
        $request->attributes->set('requestId', $requestId);
        $request->attributes->set('logId', $logId);

        $response = $next($request);

        return $response;
    }
}

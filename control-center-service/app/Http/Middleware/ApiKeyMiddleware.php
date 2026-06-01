<?php

namespace App\Http\Middleware;

use App\Enums\AppAuthResponseCode;
use App\Exceptions\AppControlCenterException;
use App\Traits\LogAudit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware {
    use LogAudit;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        $apiKeyClient = $request->header('X-API-Key-Client');
        if (!$apiKeyClient)
            throw new AppControlCenterException(
                codeName: AppAuthResponseCode::Unauthorized,
                context: LogAudit::setContexLog(
                    isLog: true,
                    level: 'warning',
                    message: 'Unauthorized: Request Not Found API Key Client'
                )
            );

        $apiKeyService = config('services.application.api_key');
        if (!$apiKeyService)
            throw new AppControlCenterException(
                codeName: AppAuthResponseCode::Unauthorized,
                context: LogAudit::setContexLog(
                    isLog: true,
                    isNotify: true,
                    level: 'warning',
                    message: 'Unauthorized: Api Key Target Service not initialized'
                )
            );

        if ($apiKeyService !== $apiKeyClient)
            throw new AppControlCenterException(
                codeName: AppAuthResponseCode::Unauthorized,
                context: LogAudit::setContexLog(
                    isLog: true,
                    level: 'warning',
                    message: 'Unauthorized: Invalid API Key'
                )
            );

        return $next($request);
    }
}

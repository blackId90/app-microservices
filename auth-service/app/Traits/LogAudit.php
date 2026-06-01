<?php

namespace App\Traits;

use App\Enums\AppAuthResponseCode;
use App\Exceptions\AppAuthException;
use App\Services\DiscordNotifierService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait LogAudit {
    public static function setContexLog(?AppAuthResponseCode $aliasCodeName = null, array $extraContextInternal = [], bool $isLog = false, bool $isNotify = false, string $level = 'error', ?string $message = null, array $extraContextLog = []): array {
        $log = [];

        //* Add internal hanya jika ada aliasCodeName atau extraContext
        if ($aliasCodeName !== null || !empty($extraContext))
            $log['internal'] = [
                'codeName' => $aliasCodeName,
                'extraContextInternal' => $extraContextInternal,
            ];

        //* Add additionalLog hanya jika ada flag/log/notify/message
        if ($isLog || $isNotify || $message !== null)
            $log['additionalLog'] = [
                'isLog' => $isLog,
                'isNotify' => $isNotify,
                'level' => $level,
                'message' => $message,
                'extraContextLog' => $extraContextLog
            ];

        return ['log' => $log];
    }

    public static function logAudit(AppAuthException $appAuthException, string $level, string $message, array $extraContext = []): void {
        $request = request();

        $requestId = $request->attributes->get('requestId');
        $logId = $request->attributes->get('logId');
        $serviceName = Str::studly(config('app.name'));
        $requestSource = $request->header('X-Request-Source');

        $method = $request->method();
        $path = $request->path();
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $authUserId = $request->attributes->get('userId', 'Guest');
        $authRoleId = $request->attributes->get('roleId', null);
        $userId = $request->user()?->getAuthIdentifier();

        $httpStatusCode = $appAuthException->getCode();
        $errorFile = $appAuthException->getFile();
        $errorLine = $appAuthException->getLine();

        $trace = collect($appAuthException->getTrace())->map(function ($t) {
            $file = $t['file'] ?? '[internal]';
            $line = $t['line'] ?? '';
            $func = $t['function'] ?? '';
            $class = $t['class'] ?? '';

            return "{$file}({$line}): {$class}{$func}()";
        })->take(10)->toArray();

        //* 📝 Format log Laravel with indents
        $logText = "🔴 [" . strtoupper($level) . "] [{$httpStatusCode}] [{$requestId}]:\n"
            . "🔹 Log ID: {$logId}\n"
            . "🔹 Request ID: {$requestId}\n"
            . "🔹 Service: {$serviceName}\n"
            . "🔹 Request Source: {$requestSource}\n"
            . "🔹 Access: [{$method}] {$path}\n"
            . "🔹 Auth User ID: {$authUserId}\n"
            . "🔹 Auth Role ID: {$authRoleId}\n"
            . "🔹 User ID: {$userId}\n"
            . "🔹 IP Address: {$ipAddress}\n"
            . "🔹 Device: {$userAgent}\n";

        // ✅ Additional custom extra context not empty
        if (!empty($extraContext)) {
            $logText .= "🔹 Extra Context:\n";
            foreach ($extraContext as $key => $value) {
                $logText .= " - {$key}: {$value}\n";
            }
        }

        $logText .= "🔹 Error: {$message} at {$errorFile}:{$errorLine}\n"
            . "🔹 Stack trace:\n";
        foreach ($trace as $i => $line) {
            $logText .= "   #{$i} {$line}\n";
        }

        //* ✅ Create log laravel with formatter
        Log::{$level}(rtrim($logText));

        unset($trace, $extraContext);
    }

    public static function notifyAudit(AppAuthException $appAuthException, string $level, string $message): void {
        $request = request();

        $reqTimestamp = $request->attributes->get('requestTimestamp');
        $now = Carbon::parse($reqTimestamp);

        $requestId = $request->attributes->get('requestId');
        $logId = $request->attributes->get('logId');
        $serviceName = Str::studly(config('app.name'));
        $requestSource = $request->header('X-Request-Source');

        $method = $request->method();
        $path = $request->path();
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $authUserId = $request->attributes->get('userId', 'Guest');

        $httpStatusCode = $appAuthException->getCode();
        $errorFile = $appAuthException->getFile();
        $errorLine = $appAuthException->getLine();

        $context = [
            'request_id' => $requestId,
            'log_id' => $logId,
            'service' => $serviceName,
            'request_source' => $requestSource,
            'status' => $httpStatusCode,
            'level' => $level,
            'file' => $errorFile,
            'line' => $errorLine,
            'message' => $message,
            'method' => $method,
            'url' => $path,
            'user_id' => $authUserId,
            'ip' => $ipAddress,
            'user_agent' => $userAgent,
            'date_now' => $now
        ];

        //* 🚀 Send embed to Discord
        DiscordNotifierService::sendEmbed($context);

        unset($context);
    }
}

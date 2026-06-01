<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Services\DiscordNotifierService;

trait ExceptionLogger {
    public static function logAndNotify(Throwable $e, int $httpStatusCode): void {
        $level = self::resolveLogLevel($httpStatusCode); // success, warning, error, info, debug

        $req = request();
        $reqTimestamp = $req->attributes->get('requestTimestamp');
        $now = Carbon::parse($reqTimestamp);
        $requestId = $req->attributes->get('requestId');
        $logId = $req->attributes->get('logId');

        $method = $req->method();
        $path = $req->path();
        $ipAddress = $req->ip();
        $userAgent = $req->userAgent();
        $userId = $req->attributes->get('userId') ?? 'Guest';

        $contextLog = $e instanceof \App\Exceptions\AppAuthException ? $e->getContextLogCodeName() : null;
        $errorCodeName = $contextLog ? $contextLog->value : $e->getMessage();
        // $errorMessage = $e->getMessage();
        $errorFile = $e->getFile();
        $errorLine = $e->getLine();

        $trace = collect($e->getTrace())->map(function ($t) {
            $file = $t['file'] ?? '[internal]';
            $line = $t['line'] ?? '';
            $func = $t['function'] ?? '';
            $class = $t['class'] ?? '';

            return "{$file}({$line}): {$class}{$func}()";
        // })->toArray();
        })->take(10)->toArray();

        //* 📝 Format log Laravel dengan indentasi
        $logText = "🔴 [" . strtoupper($level) . "] [{$httpStatusCode}] [{$requestId}]:\n"
            . "🔹 Log ID: {$logId}\n"
            . "🔹 Request ID: {$requestId}\n"
            . "🔹 Access: [{$method}] {$path}\n"
            . "🔹 User: {$userId}\n"
            . "🔹 IP Address: {$ipAddress}\n"
            . "🔹 Device: {$userAgent}\n"
            . "🔹 Error: {$errorCodeName} at {$errorFile}:{$errorLine}\n"
            . "🔹 Stack trace:\n";

        foreach ($trace as $i => $line) {
            $logText .= "   #{$i} {$line}\n";
        }

        //* ✅ Create log laravel with formatter
        Log::{$level}(rtrim($logText));

        $context = [
            'request_id' => $requestId,
            'log_id' => $logId,
            'status' => $httpStatusCode,
            'level' => $level,
            'file' => $errorFile,
            'line' => $errorLine,
            'message' => $errorCodeName,
            'method' => $method,
            'url' => $path,
            'user_id' => $userId,
            'ip' => $ipAddress,
            'user_agent' => $userAgent,
            'date_now' => $now
        ];

        //* 🚀 Kirim embed ke Discord
        DiscordNotifierService::sendEmbed($context);

        unset($trace, $context);
    }

    protected static function resolveLogLevel(int $statusCode): string {
        return match (true) {
            $statusCode >= 500 => 'error',
            $statusCode >= 400 => 'warning',
            $statusCode >= 300 => 'notice',
            $statusCode >= 200 => 'info',
            default => 'debug',
        };
    }
}

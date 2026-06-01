<?php

namespace App\Exceptions;

use App\Traits\ApiResponseFormatter;
use App\Traits\ExceptionLogger;
use App\Traits\HasErrorCode;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler {
    use ApiResponseFormatter, HasErrorCode, ExceptionLogger;

    public static function handle(Exceptions $exceptions): void {
        //* 📤 Reportable: kirim ke Discord jika status ≥ 500
        $exceptions->reportable(function (Throwable $e) {
            // dd($e);
            $httpStatusCode = self::resolveStatusCode($e);

            if ($httpStatusCode < 500)
                return;

            // if ($httpStatusCode >= 500)
            self::logAndNotify($e, $httpStatusCode);
        })->stop();

        //* 🧱 Fallback untuk semua exception
        $exceptions->renderable(function (Throwable $e, Request $request) {
            $result = self::generateResponse($e);

            if ($request->expectsJson()) {
                return (new self)->formatResponse(
                    status: $result['status'],
                    message: $result['message'],
                    codeName: $result['codeName'],
                    errors: $result['context']
                );
            }
        });
    }

    protected static function resolveStatusCode(Throwable $e): int {
        if ($e instanceof HttpExceptionInterface && $e->getStatusCode() !== 0)
            return $e->getStatusCode();

        return method_exists($e, 'getCode') && $e->getCode() !== 0 && $e->getCode() !== "XX000" ? $e->getCode() : 500;
    }

    protected static function generateResponse(Throwable $e) {
        if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            $httpStatusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

            self::logAndNotify($e, $httpStatusCode);

            return [
                'status' => $httpStatusCode,
                'codeName' => 'not_found',
                'message' => trans(key: 'exceptions.not_found', locale: request()->header('Accept-Language', config('app.locale'))),
                'context' => []
            ];
        }

        $enum = $e instanceof \App\Exceptions\AppControlCenterException ? $e->getErrorEnum() : null;

        // ✅ Status code aman terhadap uninitialized property
        $status = $enum?->getStatusCode() ?? 500;

        // ✅ Code name dari trait atau fallback
        $codeName = $enum?->value ?? 'unexpected_error';

        // ✅ Pesan error dari enum atau fallback
        $message = $enum?->getMessage() ?? trans(key: "exceptions.unexpected_error", locale: request()->header('Accept-Language', config('app.locale'))); // $message = $enum?->getMessage() ?? ($e->getMessage() ?: 'An unexpected error occurred');

        $context = $e instanceof \App\Exceptions\AppControlCenterException ? $e->getContext() : [];

        //* Remove context logs in mode production
        $isShowDebug = app()->isProduction();
        if ($isShowDebug && (!empty($context) && isset($context['log'])))
            unset($context['log']);

        return ['status' => $status, 'codeName' => $codeName, 'message' => $message, 'context' => $context];
    }
}

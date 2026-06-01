<?php

namespace App\Exceptions;

use App\Enums\AppAuthResponseCode;
use App\Traits\ApiResponseFormatter;
use App\Traits\ExceptionLogger;
use App\Traits\HasErrorCode;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
// use Tymon\JWTAuth\Exceptions\JWTException;
// use Illuminate\Database\QueryException;
// use Illuminate\Redis\RedisConnectionException;
use Throwable;

class Handler {
    use ApiResponseFormatter, HasErrorCode, ExceptionLogger;

    public static function handle(Exceptions $exceptions): void {
        /*
        //* 🔧 Tangani UserNotFoundFromTokenException
        $exceptions->renderable(function (UserNotFoundFromTokenException $e, Request $request) {
            return (new self)->formatResponse(
                status: $e->getCode(),
                message: $e->getMessage(),
                data: null,
                codeName: $e->getCodeName()
            );
        });

        //* 🔧 Tangani BannedTokenException
        $exceptions->renderable(function (BannedTokenException $e, Request $request) {
            return (new self)->formatResponse(
                status: $e->getCode(),
                message: $e->getMessage(),
                data: null,
                codeName: $e->getCodeName()
            );
        });
        */

        /*
        //* 🔐 JWT
        $exceptions->renderable(function (JWTException $e, Request $request) {
            return response()->json(['error' => 'Token error', 'message' => $e->getMessage()], 401);
        });

        //* 🐘 PostgreSQL
        $exceptions->renderable(function (QueryException $e, Request $request) {
            return response()->json(['error' => 'Database error', 'message' => $e->getMessage()], 500);
        });

        //* ⚡ Redis
        $exceptions->renderable(function (RedisConnectionException $e, Request $request) {
            return response()->json(['error' => 'Redis error', 'message' => $e->getMessage()], 503);
        });

        //* 🌐 HTTP
        $exceptions->renderable(function (HttpExceptionInterface $e, Request $request) {
            return response()->json(['error' => 'HTTP error', 'message' => $e->getMessage()], $e->getStatusCode());
        });
        */

        /*
        //* Handle for ValidationException
        $exceptions->renderable(function (ValidationException $e, $request) {
            $errors = collect($e->errors())
                ->map(fn($messages) => $messages[0])
                ->toArray();

            $enum = AppAuthResponseCode::UnprocessableContent;

            // ✅ Status code aman terhadap uninitialized property
            $status = $enum?->getStatusCode() ?? 400;

            // ✅ Code name dari trait atau fallback
            $codeName = $enum?->value ?? 'bad_request';

            // ✅ Pesan error dari enum atau fallback
            $message = $enum?->getMessage() ?? 'bad_request';

            if ($request->expectsJson()) {
                return (new self)->formatResponse(
                    status: $status,
                    message: $message,
                    codeName: $codeName,
                    errors: $errors
                );
            }
        });

        //* Handle for AccessDeniedHttpException
        $exceptions->renderable(function (AccessDeniedHttpException $e, $request) {
            $enum = AppAuthResponseCode::Forbidden;

            // ✅ Status code aman terhadap uninitialized property
            $status = $enum?->getStatusCode() ?? 403;

            // ✅ Code name dari trait atau fallback
            $codeName = $enum?->value ?? 'forbidden';

            // ✅ Pesan error dari enum atau fallback
            $message = $enum?->getMessage() ?? 'forbidden';

            if ($request->expectsJson()) {
                return (new self)->formatResponse(
                    status: $status,
                    message: $message,
                    codeName: $codeName
                );
            }
        });
        */

        //* 📤 Reportable: kirim ke Discord jika status ≥ 500
        $exceptions->reportable(function (Throwable $e) {
            $httpStatusCode = self::resolveStatusCode($e);

            if ($httpStatusCode < 500)
                return;

            // if ($httpStatusCode >= 500)
            self::logAndNotify($e, $httpStatusCode);
        })->stop();

        //* 🧱 Fallback untuk semua exception
        $exceptions->renderable(function (Throwable $e, Request $request) {
            $result = self::generateResponse($e);

            /*
            if ($request->expectsJson()) {
                return (new self)->formatResponse(
                    status: $result['status'],
                    message: $result['message'],
                    codeName: $result['codeName'],
                    errors: $result['context']
                );
            }
            */
            return (new self)->formatResponse(
                status: $result['status'],
                message: $result['message'],
                codeName: $result['codeName'],
                errors: $result['context']
            );
        });
    }

    protected static function resolveStatusCode(Throwable $e): int {
        if ($e instanceof HttpExceptionInterface && $e->getStatusCode() !== 0)
            return $e->getStatusCode();

        return method_exists($e, 'getCode') && $e->getCode() !== 0 ? $e->getCode() : 500;
    }

    protected static function generateResponse(Throwable $e) {
        if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            $enumName = AppAuthResponseCode::NotFound;
            $httpStatusCode = $enumName->getStatusCode() ?? method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            // $httpStatusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

            self::logAndNotify($e, $httpStatusCode);

            return [
                'status' => $httpStatusCode,
                'codeName' => $enumName, // 'not_found',
                'message' => $enumName->getMessage(), // trans(key: 'exceptions.not_found', locale: request()->header('Accept-Language', config('app.locale'))),
                'context' => []
            ];
        }

        if ($e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException) {
            $enumName = AppAuthResponseCode::Forbidden;

            return [
                'status' => $enumName->getStatusCode(),
                'codeName' => $enumName,
                'message' => $enumName->getMessage(),
                'context' => []
            ];
        }

        if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
            $enumName = AppAuthResponseCode::MethodNotAllowed;

            return [
                'status' => $enumName->getStatusCode(),
                'codeName' => $enumName,
                'message' => $enumName->getMessage(),
                'context' => []
            ];
        }

        if ($e instanceof \Illuminate\Routing\Exceptions\InvalidSignatureException) {
            $httpStatusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

            // self::logAndNotify($e, $httpStatusCode);

            return [
                'status' => $httpStatusCode,
                'codeName' => 'invalid_signature',
                'message' => trans(key: 'exceptions.invalid_signature', locale: request()->header('Accept-Language', config('app.locale'))),
                'context' => []
            ];
        }

        //* Custom response for too many attempts
        if ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
            $httpStatusCode = 429;

            // self::logAndNotify($e, $httpStatusCode);

            return [
                'status' => $httpStatusCode,
                'codeName' => 'too_many_attempts',
                'message' => trans(key: 'exceptions.too_many_attempts', locale: request()->header('Accept-Language', config('app.locale'))),
                'context' => []
            ];
        }

        $enum = $e instanceof \App\Exceptions\AppAuthException ? $e->getErrorEnum() : null;

        // ✅ Status code aman terhadap uninitialized property
        $status = $enum?->getStatusCode() ?? 500;

        // ✅ Code name dari trait atau fallback
        $codeName = $enum?->value ?? 'unexpected_error';

        // ✅ Pesan error dari enum atau fallback
        $message = $enum?->getMessage() ?? trans(key: "exceptions.unexpected_error", locale: request()->header('Accept-Language', config('app.locale'))); // $message = $enum?->getMessage() ?? ($e->getMessage() ?: 'An unexpected error occurred');

        $context = $e instanceof \App\Exceptions\AppAuthException ? $e->getContext() : [];

        return ['status' => $status, 'codeName' => $codeName, 'message' => $message, 'context' => $context];
    }
}

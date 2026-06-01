<?php

namespace App\Exceptions;

use App\Enums\AppAuthResponseCode;
// use Illuminate\Http\Request;

class JWTAuthException extends AppAuthException {
    public function __construct(?AppAuthResponseCode $codeName = AppAuthResponseCode::Unauthorized, ?int $status = 401, ?array $context = []) {
        $statusCode = $codeName?->getStatusCode() ?? $status;

        parent::__construct(
            codeName: $codeName,
            status: $statusCode,
            context: $context
        );
    }

    /*
    public function render(Request $request) {
        if ($request->expectsJson()) {
            $enum = $this->getErrorEnum();

            // ✅ Status code aman terhadap uninitialized property
            $status = $enum?->getStatusCode() ?? 500;

            // ✅ Code name dari trait atau fallback
            $codeName = $enum?->value ?? 'unexpected_error';

            // ✅ Pesan error dari enum atau fallback
            $message = $enum?->getMessage() ?? 'An unexpected error occurred';

            return $this->formatResponse(
                status: $status,
                message: $message,
                codeName: $codeName,
                errors: $this->getContext()
            );
        }
    }
    */
}

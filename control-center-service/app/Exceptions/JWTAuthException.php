<?php

namespace App\Exceptions;

use App\Enums\AppAuthResponseCode;

class JWTAuthException extends AppControlCenterException {
    public function __construct(?AppAuthResponseCode $codeName = AppAuthResponseCode::Unauthorized, ?int $status = 401, ?array $context = []) {
        $statusCode = $codeName?->getStatusCode() ?? $status;

        parent::__construct(
            codeName: $codeName,
            status: $statusCode,
            context: $context
        );
    }
}

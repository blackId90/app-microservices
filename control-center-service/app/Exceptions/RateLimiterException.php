<?php

namespace App\Exceptions;

use App\Enums\AppAuthResponseCode;

class RateLimiterException extends AppControlCenterException {
    public function __construct(?AppAuthResponseCode $codeName = AppAuthResponseCode::UserToManyRequest, ?int $status = 429, ?array $context = []) {
        $statusCode = $codeName?->getStatusCode() ?? $status;

        parent::__construct(
            codeName: $codeName,
            status: $statusCode,
            context: $context
        );
    }
}

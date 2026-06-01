<?php

namespace App\Exceptions;

use App\Enums\AppAuthResponseCode;

class TokenBlacklistedException extends AppAuthException {
    public function __construct(?AppAuthResponseCode $codeName = AppAuthResponseCode::BannedToken, ?int $status = 403, ?array $context = []) {
        $statusCode = $codeName?->getStatusCode() ?? $status;

        parent::__construct(
            codeName: $codeName,
            status: $statusCode,
            context: $context
        );
    }
}

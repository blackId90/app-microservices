<?php

namespace App\Exceptions;

use App\Enums\AppAuthResponseCode;

class UserNotFoundFromTokenException extends AppControlCenterException {
    public function __construct(?AppAuthResponseCode $codeName = AppAuthResponseCode::UserNotFoundFromToken, ?int $status = 404, ?array $context = []) {
        parent::__construct(
            codeName: $codeName,
            status: $status,
            context: $context
        );
    }
}

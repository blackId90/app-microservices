<?php

namespace App\Exceptions;

use App\Enums\AppAuthResponseCode;

class ValidationFormRequestException extends AppControlCenterException {
    public function __construct(?AppAuthResponseCode $codeName = AppAuthResponseCode::UnprocessableContent, ?int $status = 422, ?array $context = []) {
        $statusCode = $codeName?->getStatusCode() ?? $status;

        parent::__construct(
            codeName: $codeName,
            status: $statusCode,
            context: $context
        );
    }
}

<?php

namespace App\Enums;

enum CompanyInvoiceStatusEnum: string {
    case PAID = 'paid';
    case PENDING = 'pending';
    case FAILED = 'failed';

    public function label(): string {
        return match ($this) {
            self::PAID => 'Paid',
            self::PENDING => 'Pending',
            self::FAILED => 'Failed',
        };
    }
}

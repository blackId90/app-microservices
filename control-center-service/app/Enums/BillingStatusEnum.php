<?php

namespace App\Enums;

enum BillingStatusEnum: string {
    case TRIAL = 'trial';
    case PAID = 'paid';
    case UNPAID = 'unpaid';
    case EXPIRED = 'expired';
    case SUSPENDED = 'suspended';

    public function label(): string {
        return match ($this) {
            self::TRIAL => 'Trial',
            self::PAID => 'Paid',
            self::UNPAID => 'Unpaid',
            self::EXPIRED => 'Expired',
            self::SUSPENDED => 'Suspended',
        };
    }

    public function color(): string {
        return match ($this) {
            self::TRIAL => 'blue',
            self::PAID => 'green',
            self::UNPAID => 'yellow',
            self::EXPIRED => 'orange',
            self::SUSPENDED => 'red',
        };
    }

    public function canAccess(): bool {
        return match ($this) {
            self::TRIAL, self::PAID => true,
            default => false,
        };
    }
}

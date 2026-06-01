<?php

namespace App\Enums;

enum UserStatusEnum: string {
    case PENDING = '-1';
    case INACTIVE = '0';
    case ACTIVE = '1';

    public function label(): string {
        return match ($this) {
            self::PENDING => 'Pending Verification',
            self::INACTIVE => 'Inactive',
            self::ACTIVE => 'Active',
        };
    }
}

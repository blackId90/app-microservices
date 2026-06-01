<?php

namespace App\Enums;

enum PlanTypeEnum: string {
    CASE DEVELOPMENT = 'development';
    case BASIC = 'basic';
    case PRO = 'pro';
    case ENTERPRISE = 'enterprise';

    public function label(): string {
        return match ($this) {
            self::DEVELOPMENT => 'Development',
            self::BASIC => 'Basic',
            self::PRO => 'Professional',
            self::ENTERPRISE => 'Enterprise',
        };
    }

    /*
    public function price(): float {
        return match ($this) {
            self::BASIC => 99_000.00,
            self::PRO => 299_000.00,
            self::ENTERPRISE => 999_000.00,
        };
    }

    public function maxUsers(): int {
        return match ($this) {
            self::BASIC => 10,
            self::PRO => 50,
            self::ENTERPRISE => 9999, // Unlimited
        };
    }

    public function features(): array {
        return match ($this) {
            self::BASIC => ['basic_support', '5gb_storage', 'basic_analytics'],
            self::PRO => ['priority_support', '50gb_storage', 'advanced_analytics', 'api_access'],
            self::ENTERPRISE => ['24/7_support', 'unlimited_storage', 'custom_analytics', 'api_access', 'sla'],
        };
    }
    */
}

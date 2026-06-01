<?php

namespace App\Enums;

enum CompanyEventTypeEnum: string {
    case PROVISIONING = 'provisioning'; // Tenant baru dibuat
    case MIGRATION = 'migration'; // Membuat schema database tenant
    case SEED = 'seed'; // Insert data default
    case SUSPENSION = 'suspension'; // Tenant suspended karena expired
    case BILLING = 'billing'; // Pembayaran invoice
    case PERMISSION_UPDATE = 'permission_update'; // Admin update permission tenant

    public function label(): string {
        return match ($this) {
            self::PROVISIONING => 'Provisioning',
            self::MIGRATION => 'Migration',
            self::SEED => 'Seed',
            self::SUSPENSION => 'Suspension',
            self::BILLING => 'Billing',
            self::PERMISSION_UPDATE => 'Permission Update'
        };
    }
}

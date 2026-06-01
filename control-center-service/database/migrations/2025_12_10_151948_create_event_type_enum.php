<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Keterangan:
     * provisioning: Tenant baru dibuat
     * migration: Membuat schema database tenant
     * seed: Insert data default
     * suspension: Tenant suspended karena expired
     * billing: Pembayaran invoice
     * permission_update: Admin update permission tenant
     */
    public function up(): void {
        // Buat ENUM di PostgreSQL
        DB::statement("DROP TYPE IF EXISTS enum_event_type CASCADE");
        DB::statement("CREATE TYPE enum_event_type AS ENUM ('provisioning', 'migration', 'seed', 'suspension', 'billing', 'permission_update')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // Drop ENUM di PostgreSQL jika rollback
        DB::statement("DROP TYPE IF EXISTS enum_event_type");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    /**
     * Run the migrations.
     * Keterangan:
     * -1: Email not verified (Pending)
     * 0: Inactive
     * 1: Active
     */
    public function up(): void {
        // Buat ENUM di PostgreSQL
        DB::statement("DROP TYPE IF EXISTS enum_billing_status CASCADE");
        DB::statement("CREATE TYPE enum_billing_status AS ENUM ('trial', 'unpaid', 'paid', 'expired', 'suspended')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // Drop ENUM di PostgreSQL jika rollback
        DB::statement("DROP TYPE IF EXISTS enum_billing_status");
    }
};

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
        DB::statement("DROP TYPE IF EXISTS enum_payment_method CASCADE");
        DB::statement("CREATE TYPE enum_payment_method AS ENUM ('cash', 'credit_card', 'bank_transfer', 'virtual_account', 'ewallet', 'paylater')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // Drop ENUM di PostgreSQL jika rollback
        DB::statement("DROP TYPE IF EXISTS enum_payment_method");
    }
};

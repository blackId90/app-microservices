<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        // Buat ENUM di PostgreSQL
        DB::statement("DROP TYPE IF EXISTS enum_invoice_status CASCADE");
        DB::statement("CREATE TYPE enum_invoice_status AS ENUM ('paid', 'pending', 'failed')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // Drop ENUM di PostgreSQL jika rollback
        DB::statement("DROP TYPE IF EXISTS enum_invoice_status");
    }
};

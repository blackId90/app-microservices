<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        // Buat ENUM di PostgreSQL
        DB::statement("DROP TYPE IF EXISTS enum_permission_type CASCADE");
        DB::statement("CREATE TYPE enum_permission_type AS ENUM ('header', 'group', 'parent', 'item')");

        DB::statement("DROP TYPE IF EXISTS enum_permission_target CASCADE");
        DB::statement("CREATE TYPE enum_permission_target AS ENUM ('_self', '_blank')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // Drop ENUM di PostgreSQL jika rollback
        DB::statement("DROP TYPE IF EXISTS enum_permission_type");
        DB::statement("DROP TYPE IF EXISTS enum_permission_target");
    }
};

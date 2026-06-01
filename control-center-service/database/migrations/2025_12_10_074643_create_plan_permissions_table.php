<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('plan_permissions', function (Blueprint $table) {
            $table->uuid('plan_permissions_plan_id');
            $table->uuid('plan_permissions_permission_id')->comment('foregin key auth_db.auth_permissions(permission_id)');

            //* Primary key gabungan
            $table->primary(['plan_permissions_plan_id', 'plan_permissions_permission_id']);

            //* Index gabungan sesuai schema
            $table->index(['plan_permissions_plan_id', 'plan_permissions_permission_id'], 'plan_permissions_plan_permission_index');

            /*
            //* Foreign key ke auth_permissions (diasumsikan ada di schema auth_db)
            $table->foreign('plan_permissions_permission_id')
                ->references('auth_permission_id')
                ->on('auth_permissions')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            */
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('plan_permissions');
    }
};

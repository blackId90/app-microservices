<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('auth_role_permissions', function (Blueprint $table) {
            $table->uuid('auth_role_permission_role_id');
            $table->uuid('auth_role_permission_permission_id');
            $table->smallInteger('auth_role_permission_parameter')->nullable()->comment('LIST => 1: Without Deleted, 2: Deleted Data, 3: All Data. READ => 1: Without Deleted, 2: With Deleted. UPDATE => 1: Without Deleted, 2: With Deleted. DELETE => 1: Soft Deleted, 2: Restore from Trash, 3: Deleted from Trash, 4: Permanent Deleted');
            $table->primary(['auth_role_permission_role_id', 'auth_role_permission_permission_id']);

            //* Foreign Key
            $table->foreign('auth_role_permission_role_id')
                ->references('auth_role_id')->on('auth_roles')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->foreign('auth_role_permission_permission_id')
                ->references('auth_permission_id')->on('auth_permissions')
                ->restrictOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('auth_role_permissions');
    }
};

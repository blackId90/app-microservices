<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('auth_roles', function (Blueprint $table) {
            $table->uuid('auth_role_id')->primary();
            $table->string('auth_role_slug', 150)->unique();
            $table->string('auth_role_name', 200);
            $table->boolean('auth_role_is_active')->default(true);
            $table->timestamp('created_at', 6)->useCurrent();
            $table->timestamp('updated_at', 6)->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at', 6)->nullable();

            //* Index
            $table->index('auth_role_slug');
            $table->index('auth_role_is_active');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('auth_roles');
    }
};

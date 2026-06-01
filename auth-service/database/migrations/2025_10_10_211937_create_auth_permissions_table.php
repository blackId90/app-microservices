<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('auth_permissions', function (Blueprint $table) {
            $table->uuid('auth_permission_id')->primary();
            $table->rawColumn('auth_permission_type', 'enum_permission_type');
            $table->uuid('auth_permission_parent_permission_id')->nullable()->comment('Child module permission_id');
            $table->string('auth_permission_slug', 100)->unique();
            $table->string('auth_permission_title', 100);
            $table->string('auth_permission_icon', 50)->nullable();
            $table->string('auth_permission_color', 50)->nullable();
            $table->string('auth_permission_url', 100)->nullable();
            $table->string('auth_permission_route', 100)->nullable();
            $table->rawColumn('auth_permission_target', 'enum_permission_target')->default('_self');
            $table->integer('auth_permission_order')->nullable();
            $table->boolean('auth_permission_is_active')->default(true);
            $table->timestamp('created_at', 6)->useCurrent();
            $table->timestamp('updated_at', 6)->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at', 6)->nullable();

            //* Index
            $table->index('auth_permission_parent_permission_id');
            $table->index('auth_permission_slug');
            $table->index('auth_permission_is_active');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('auth_permissions');
    }
};

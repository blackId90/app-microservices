<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('sync_auth_users', function (Blueprint $table) {
            $table->uuid('auth_user_id')->primary();
            $table->string('auth_user_email', 100)->unique();
            $table->string('auth_user_username', 100)->unique();
            $table->uuid('auth_user_company_id')->nullable();
            $table->boolean('auth_user_is_admin')->default(false);
            $table->rawColumn('auth_user_is_status', 'enum_user_status')
                ->default(-1)
                ->comment('-1: Email not verified (Pending), 0: Inactive, 1: Active');
            /*
            $table->addColumn('enum_user_status', 'auth_user_is_status')
                ->default(-1)
                ->comment('-1: Pending, 0: Inactive, 1: Active');
            */
            $table->timestamp('created_at', 6)->useCurrent();
            $table->timestamp('updated_at', 6)->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at', 6)->nullable();

            //* Index
            $table->index('auth_user_email');
            $table->index('auth_user_username');
            $table->index('auth_user_company_id');
            $table->index('auth_user_is_status');
            $table->index('deleted_at');

            /*
            //* Foreign Key
            $table->foreign('auth_user_company_id')
                ->references('company_id')->on('companies')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            */
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('sync_auth_users');
    }
};

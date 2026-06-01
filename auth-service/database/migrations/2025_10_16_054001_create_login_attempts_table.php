<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->uuid('login_attempt_id')->primary();
            $table->rawColumn('login_attempt_type', 'enum_login_attempt_type')->comment('login: log for action login, refresh: log for action refresh token, logout: log for action logout');
            $table->string('login_attempt_identifier');
            $table->ipAddress('login_attempt_ip_address');
            $table->string('login_attempt_user_agent', 255);
            $table->boolean('login_attempt_is_status')->default(false)->comment('0: Failed, 1: Success');
            $table->uuid('created_by')->nullable()->comment('Only for successful logins');

            $table->timestamp('created_at', 6)->useCurrent();
            $table->timestamp('updated_at', 6)->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at', 6)->nullable();

            //* Indexes
            $table->index('login_attempt_type');
            $table->index('created_by');
            $table->index('created_at');

            //* Foreign key constraint
            $table->foreign('created_by')
                ->references('auth_user_id')->on('auth_users')
                ->restrictOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('login_attempts');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('company_app_authentication', function (Blueprint $table) {
            $table->uuid('company_app_authentication_company_id')->primary();

            //* Domain & DB
            $table->string('company_app_authentication_domain', 255)->unique();
            $table->string('company_app_authentication_db_host', 100)->unique();
            $table->smallInteger('company_app_authentication_db_port')->default(5432);
            $table->string('company_app_authentication_db_database', 100);
            $table->string('company_app_authentication_db_schema', 100)->default('public');
            $table->string('company_app_authentication_db_username', 100);
            $table->string('company_app_authentication_db_password', 255);
            $table->string('company_app_authentication_db_prefix', 100)->nullable()->default(null);

            //* Redis
            $table->string('company_app_authentication_redis_host', 100)->unique();
            $table->smallInteger('company_app_authentication_redis_port')->default(6379);
            $table->string('company_app_authentication_redis_database', 100);
            $table->string('company_app_authentication_redis_schema', 100)->default('public');
            $table->string('company_app_authentication_redis_username', 100);
            $table->string('company_app_authentication_redis_password', 255);
            $table->string('company_app_authentication_redis_prefix', 100)->nullable()->default(null);

            //* Timestamp
            $table->timestamp('created_at', 6)->useCurrent();
            $table->timestamp('updated_at', 6)->useCurrent()->useCurrentOnUpdate();

            //* Relasi ke companies
            $table->foreign('company_app_authentication_company_id')
                ->references('company_id')->on('companies')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('company_app_authentication');
    }
};

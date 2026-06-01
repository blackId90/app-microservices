<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('company_details', function (Blueprint $table) {
            $table->uuid('company_detail_company_id')->primary();
            $table->string('company_detail_facebook', 100)->nullable();
            $table->string('company_detail_twitter', 100)->nullable();
            $table->string('company_detail_instagram', 100)->nullable();
            $table->string('company_detail_linkedin', 100)->nullable();
            $table->string('company_detail_smtp_host', 50)->nullable();
            $table->smallInteger('company_detail_smtp_port')->nullable();
            $table->string('company_detail_smtp_name', 50)->nullable();
            $table->string('company_detail_smtp_user', 100)->nullable();
            $table->string('company_detail_smtp_password', 255)->nullable();

            //* Timestamp
            $table->timestamp('created_at', 6)->useCurrent();
            $table->timestamp('updated_at', 6)->useCurrent()->useCurrentOnUpdate();

            $table->foreign('company_detail_company_id')
                ->references('company_id')->on('companies')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('company_details');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Mencatat aktivitas (DB dibuat, migration sukses, seeder) yang terjadi pada level tenant/company
     */
    public function up(): void {
        Schema::create('company_events', function (Blueprint $table) {
            $table->uuid('company_event_id')->primary();

            //* Foreign key ke companies
            $table->uuid('company_event_company_id');
            $table->rawColumn('company_event_type', 'enum_event_type');
            $table->string('company_event_description', 255)->comment('ex: Database tenant_123 berhasil dibuat dan migration selesai');
            $table->jsonb('company_event_metadata')->nullable();
            $table->smallInteger('company_event_status')->default(-1)->comment('-1: Pending, 0: Failed, 1: Success');
            $table->uuid('created_by')->nullable();

            //* Timestamp
            $table->timestamp('created_at', 6)->useCurrent();
            $table->timestamp('updated_at', 6)->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at', 6)->nullable();

            //* Foreign key constraints
            $table->foreign('company_event_company_id')
                ->references('company_id')
                ->on('companies')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            /*
            //* Foreign key ke auth_users (Karena created_by merujuk ke auth_users di service auth, maka tidak bisa dibuat foreign key).
            $table->foreign('created_by')
                ->references('auth_user_id')
                ->on('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            */

            //* Indexes
            $table->index('company_event_company_id', 'company_event_company_id_index');
            $table->index('company_event_type', 'company_event_type_index');
            $table->index('company_event_status', 'company_event_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('company_events');
    }
};

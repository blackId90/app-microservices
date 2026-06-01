<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('company_id')->primary();

            $table->string('company_logo', 255)->nullable();
            $table->string('company_name', 150);
            $table->string('company_address', 255);
            $table->unsignedBigInteger('company_village_id');
            $table->string('company_zip_code', 10)->nullable()->default(null);
            $table->string('company_fax', 15)->nullable();
            $table->string('company_phone', 15)->unique();
            $table->string('company_website', 100)->nullable()->default(null);
            $table->string('company_email', 100)->unique();
            $table->string('company_key_email', 200);
            $table->timestamp('company_email_verified_at', 6)->nullable();
            $table->rawColumn('company_is_status', 'enum_company_status')->default('-1')->comment('-1: Email not verified (Pending), 0: Inactive, 1: Active');
            $table->decimal('company_base_price', 12, 2)->default(0.00)->comment('Harga per bulan');
            $table->smallInteger('company_billing_cycle')->default(1)->comment('Jumlah bulan dibayar sekaligus');
            $table->rawColumn('company_billing_status', 'enum_billing_status')->default('trial')->comment('trial, unpaid, paid, expired, suspended');
            $table->timestamp('company_trial_ends_at', 6)->nullable()->comment('Trial berakhir pada');
            $table->timestamp('company_paid_ends_at', 6)->nullable()->comment("Paid berakhir pada");

            //* Timestamp
            $table->timestamp('created_at', 6)->useCurrent();
            $table->timestamp('updated_at', 6)->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at', 6)->nullable();

            $table->index('company_village_id', 'company_village_id_index');
            $table->foreign('company_village_id')
                ->references('village_id')->on('reg_villages')
                ->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('companies');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Histori pembayaran
     */
    public function up(): void
    {
        Schema::create('company_invoices', function (Blueprint $table) {
            $table->uuid('company_invoice_id')->primary();

            //* Foreign key ke companies
            $table->uuid('company_invoice_company_id');
            $table->string('company_invoice_no_inv', 50)->comment("No. Invoice");
            $table->decimal('company_invoice_amount', 19, 2);
            $table->smallInteger('company_invoice_months_paid')->default(1)->comment('Jumlah bulan dibayar sekaligus');
            $table->rawColumn('company_invoice_payment_method', 'enum_payment_method')->nullable();
            $table->timestamp('company_invoice_paid_at', 6)->nullable();
            $table->timestamp('company_invoice_valid_until', 6);
            $table->rawColumn('company_invoice_status', 'enum_invoice_status')->default('pending');

            //* Timestamp
            $table->timestamp('created_at', 6)->useCurrent();
            $table->timestamp('updated_at', 6)->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at', 6)->nullable();

            //* Foreign key constraints
            $table->foreign('company_invoice_company_id')
                ->references('company_id')
                ->on('companies')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            //* Indexes
            $table->index('company_invoice_company_id', 'company_invoice_company_id_index');
            $table->index('company_invoice_status', 'company_invoice_status_index');
            $table->index('company_invoice_valid_until', 'company_invoice_valid_until_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_invoices');
    }
};

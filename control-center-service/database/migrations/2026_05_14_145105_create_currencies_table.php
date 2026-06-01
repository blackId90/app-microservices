<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('currencies', function (Blueprint $table) {
            $table->uuid('currency_id')->primary();
            $table->string('currency_code', 3)->unique();
            $table->string('currency_name', 50);
            $table->string('currency_symbol', 50);
            $table->boolean('currency_is_active')->default(true);

            //* Timestamp
            $table->timestamp('created_at', 6)->useCurrent();
            $table->timestamp('updated_at', 6)->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at', 6)->nullable();

            //* Index
            $table->index('currency_is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('currencies');
    }
};

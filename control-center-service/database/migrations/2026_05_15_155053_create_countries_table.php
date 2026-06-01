<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('countries', function (Blueprint $table) {
            $table->uuid('country_id')->primary();
            $table->char('country_code', 2)->unique();
            $table->char('country_alpha_3', 3)->unique();
            $table->string('country_name', 80);
            $table->string('country_capital', 80);
            $table->integer('country_phone'); // PostgreSQL tidak mendukung batasan panjang int(3)
            $table->string('country_continent_code', 2);
            $table->string('country_currency_code', 3);

            //* Timestamp
            $table->timestamp('created_at', 6)->useCurrent();
            $table->timestamp('updated_at', 6)->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at', 6)->nullable();

            //* Index
            $table->index('country_continent_code');
            $table->index('country_currency_code');

            //* Foreign Key
            $table->foreign('country_continent_code')
                ->references('continent_code')->on('continents')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('country_currency_code')
                ->references('currency_code')->on('currencies')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('countries');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('reg_regencies', function (Blueprint $table) {
            $table->bigIncrements('regency_id');
            $table->unsignedInteger('regency_province_id');
            $table->string('regency_name', 35);

            //* Timestamp
            $table->timestamp('created_at', 6)->useCurrent();
            $table->timestamp('updated_at', 6)->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at', 6)->nullable();

            //* Indexes
            $table->index('regency_province_id', 'regency_province_id_index');

            $table->foreign('regency_province_id')
                ->references('province_id')->on('reg_provinces')
                ->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('reg_regencies');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('reg_districts', function (Blueprint $table) {
            $table->bigIncrements('district_id');
            $table->unsignedBigInteger('district_regency_id');
            $table->string('district_name', 35);

            //* Timestamp
            $table->timestamp('created_at', 6)->useCurrent();
            $table->timestamp('updated_at', 6)->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at', 6)->nullable();

            //* Indexes
            $table->index('district_regency_id', 'district_regency_id_index');

            $table->foreign('district_regency_id')
                ->references('regency_id')->on('reg_regencies')
                ->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('reg_districts');
    }
};

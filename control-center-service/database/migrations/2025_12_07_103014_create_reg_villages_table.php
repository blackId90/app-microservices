<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('reg_villages', function (Blueprint $table) {
            $table->bigIncrements('village_id');
            $table->unsignedBigInteger('village_district_id');
            $table->string('village_name', 50);

            //* Timestamp
            $table->timestamp('created_at', 6)->useCurrent();
            $table->timestamp('updated_at', 6)->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at', 6)->nullable();

            //* Indexes
            $table->index('village_district_id', 'village_district_id_index');

            $table->foreign('village_district_id')
                ->references('district_id')->on('reg_districts')
                ->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('reg_villages');
    }
};

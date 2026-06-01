<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->uuid('user_auth_user_id')->unique();
            $table->string('user_avatar', 255)->nullable();
            $table->string('user_first_name', 100);
            $table->string('user_last_name', 100);
            $table->rawColumn('user_gender', 'enum_user_gender')->comment('male, female');
            $table->string('user_address', 255)->nullable();
            $table->unsignedBigInteger('user_village_id');
            $table->string('user_zip_code', 10)->nullable();
            $table->string('user_phone', 16)->unique();

            //* Timestamp
            $table->timestamp('created_at', 6)->useCurrent();
            $table->timestamp('updated_at', 6)->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at', 6)->nullable();

            //* Index
            $table->index('user_auth_user_id');
            $table->index('user_village_id');
            $table->index('deleted_at');

            //* Foreign Key
            /*
            $table->foreign('user_auth_user_id')
                ->references('auth_user_id')->on('sync_auth_users')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            */

            $table->foreign('user_village_id')
                ->references('village_id')->on('reg_villages')
                ->onUpdate('restrict')
                ->onDelete('restrict');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('users');
    }
};

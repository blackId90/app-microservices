<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('auth_users', function (Blueprint $table) {
            $table->uuid('auth_user_id')->primary();
            $table->string('auth_user_email', 100)->unique();
            $table->string('auth_user_username', 100)->unique();
            $table->string('auth_user_password', 255);
            $table->string('auth_user_key_email', 200);
            $table->timestamp('auth_user_email_verified_at', 6)->nullable();
            $table->uuid('auth_user_company_id')->nullable()->comment('Jika user milik tenant atau company tertentu');
            $table->uuid('auth_user_role_id');
            $table->boolean('auth_user_is_admin')->default(false);

            // $table->string('auth_user_is_status', 10)->comment('-1: Email not verified (Pending), 0: Inactive, 1: Active');
            $table->rawColumn('auth_user_is_status', 'enum_user_status')->default(-1)->comment('-1: Email not verified (Pending), 0: Inactive, 1: Active');

            $table->timestamp('created_at', 6)->useCurrent();
            $table->timestamp('updated_at', 6)->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at', 6)->nullable();

            //* Index
            $table->index('auth_user_company_id');
            $table->index('auth_user_email');
            $table->index('auth_user_username');
            $table->index('auth_user_role_id');
            $table->index('auth_user_is_status');
            $table->index('deleted_at');

            //* Foreign Key
            $table->foreign('auth_user_role_id')
                ->references('auth_role_id')->on('auth_roles')
                ->restrictOnUpdate()
                ->restrictOnDelete();
        });

        /*
        // Ubah kolom menjadi ENUM type
        DB::statement("
            ALTER TABLE auth_users
            ALTER COLUMN auth_user_is_status TYPE enum_user_status
            USING auth_user_is_status::enum_user_status
        ");

        // Set NOT NULL dan DEFAULT setelah ALTER TYPE
        DB::statement("
            ALTER TABLE auth_users
            ALTER COLUMN auth_user_is_status SET DEFAULT '-1'::enum_user_status
        ");

        DB::statement("
            ALTER TABLE auth_users
            ALTER COLUMN auth_user_is_status SET NOT NULL
        ");

        // Tambahkan comment
        DB::statement("
            COMMENT ON COLUMN auth_users.auth_user_is_status IS
            '-1: Email not verified (Pending), 0: Inactive, 1: Active'
        ");

        // Tambahkan index untuk kolom enum
        DB::statement("
            CREATE INDEX auth_users_auth_user_is_status_index
            ON auth_users(auth_user_is_status)
        ");
        */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('auth_users');
    }
};

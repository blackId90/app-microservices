<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class AppMigrate extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate {--tenant=} {--fresh} {--seed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations/seeds using pgsql-direct or tenant-specific connection';

    /**
     * Execute the console command.
     */
    public function handle() {
        $tenantId = $this->option('tenant');

        if ($tenantId) {
            //* Ambil meta koneksi tenant dari database utama
            $tenant = DB::table('company_app_authentication')->where('company_app_authentication_company_id', $tenantId)->first();

            if (!$tenant) {
                $this->error("Tenant with ID {$tenantId} not found.");
                return 1;
            }

            //* Override koneksi runtime ke tenant
            config([
                'database.connections.pgsql-direct' => [
                    'driver'   => 'pgsql',
                    'host'     => $tenant->company_app_authentication_db_host,
                    'port'     => $tenant->company_app_authentication_db_port,
                    'database' => $tenant->company_app_authentication_db_database,
                    'username' => $tenant->company_app_authentication_db_username,
                    'password' => $tenant->company_app_authentication_db_password,
                    'charset'  => 'utf8',
                    'prefix'   => $tenant->company_app_authentication_db_prefix ?? '',
                    'schema'   => $tenant->company_app_authentication_db_schema,
                    'sslmode'  => 'prefer',
                ],
                'database.default' => 'pgsql-direct',
            ]);

            DB::purge('pgsql-direct');
            DB::reconnect('pgsql-direct');

            $this->info("Running migration for tenant {$tenant->name}...");
        } else {
            //* Default ke koneksi pgsql-direct
            config(['database.default' => 'pgsql-direct']);
            $this->info("Running migration with pgsql-direct connection (default)...");
        }

        //* Jalankan migrate/fresh sesuai option
        if ($this->option('fresh')) {
            Artisan::call('migrate:fresh', ['--force' => true]);
        } else {
            Artisan::call('migrate', ['--force' => true]);
        }

        $this->line(Artisan::output());

        //* Jalankan seeder jika diminta
        if ($this->option('seed')) {
            Artisan::call('db:seed', ['--force' => true]);
            $this->line(Artisan::output());
        }

        $this->info("Done.");

        return 0;
    }
}

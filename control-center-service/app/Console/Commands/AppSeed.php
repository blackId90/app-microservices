<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class AppSeed extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed {--tenant=} {--class=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run seeders using pgsql-direct or tenant-specific connection';

    /**
     * Execute the console command.
     */
    public function handle() {
        $tenantId = $this->option('tenant');
        $class    = $this->option('class');

        if ($tenantId) {
            //* Ambil meta koneksi tenant dari database utama
            $tenant = DB::table('companies')
                ->join('company_app_authentication', 'companies.company_id', '=', 'company_app_authentication.company_app_authentication_company_id')
                ->where('companies.company_id', $tenantId)
                ->select(
                    'companies.company_name',
                    'company_app_authentication.company_app_authentication_db_host',
                    'company_app_authentication.company_app_authentication_db_port',
                    'company_app_authentication.company_app_authentication_db_database',
                    'company_app_authentication.company_app_authentication_db_username',
                    'company_app_authentication.company_app_authentication_db_password'
                )
                ->first();

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

            $this->info("Running seed for tenant {$tenant->company_name}...");
        } else {
            //* Default ke koneksi pgsql-direct
            config(['database.default' => 'pgsql-direct']);
            $this->info("Running seed with pgsql-direct connection (default)...");
        }

        //* Jalankan seeder
        $params = ['--force' => true];
        if ($class) {
            $params['--class'] = $class;
            $this->info("Running seeder {$class}...");
        } else {
            $this->info("Running all default seeders...");
        }

        Artisan::call('db:seed', $params);
        $this->line(Artisan::output());

        $this->info("Done.");

        return 0;
    }
}

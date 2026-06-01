<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class CompanyAppAuthenticationsSeeder extends Seeder {
    protected int $batchSize = 1000;

    /**
     * Run the database seeds.
     */
    public function run(): void {
        DB::table('company_app_authentication')->truncate();
        foreach ($this->generateCompanyAppAuthentications() as $chunk)
            DB::table('company_app_authentication')->insert($chunk);
    }

    private function generateCompanyAppAuthentications(): \Generator {
        $data = [
            [
                'company_app_authentication_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_app_authentication_domain' => 'company_dev_domain.dev.com',
                'company_app_authentication_db_host' => 'company_dev_db_host.cluster-abc123.us-east-1.rds.dev.com',
                // 'company_app_authentication_db_port' => '5432',
                'company_app_authentication_db_database' => 'company_dev_db_database',
                // 'company_app_authentication_db_schema' => 'public',
                'company_app_authentication_db_username' => 'company_dev_username_db',
                'company_app_authentication_db_password' => 'company_dev_password_db',
                // 'company_app_authentication_db_prefix' => null,
                'company_app_authentication_redis_host' => 'company_dev_redis_host.abc123.0001.use1.cache.dev.com',
                // 'company_app_authentication_redis_port' => '6379',
                'company_app_authentication_redis_database' => '0',
                // 'company_app_authentication_redis_schema' => 'public',
                'company_app_authentication_redis_username' => 'company_dev_username_redis',
                'company_app_authentication_redis_password' => 'company_dev_password_redis',
                // 'company_app_authentication_redis_prefix' => null,
            ]
        ];

        $collection = LazyCollection::make($data);
        $batch = [];

        foreach ($collection as $item) {
            $timestamp = Carbon::now();
            $item['created_at'] = $timestamp->format('Y-m-d H:i:s.u');
            $item['updated_at'] = $timestamp->format('Y-m-d H:i:s.u');

            $batch[] = $item;
            if (count($batch) === $this->batchSize) {
                yield $batch;
                $batch = [];
            }
        }

        if ($batch)
            yield $batch;
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class CompanyDetailsSeeder extends Seeder {
    protected int $batchSize = 1000;

    /**
     * Run the database seeds.
     */
    public function run(): void {
        DB::table('company_details')->truncate();
        foreach ($this->generateCompanyDetails() as $chunk)
            DB::table('company_details')->insert($chunk);
    }

    private function generateCompanyDetails(): \Generator {
        $data = [
            [
                'company_detail_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_detail_facebook' => 'https://www.facebook.com/company_dev',
                'company_detail_twitter' => 'https://x.com/company_dev',
                'company_detail_instagram' => 'https://www.instagram.com/company_dev',
                'company_detail_linkedin' => 'https://www.linkedin.com/company_dev',
                'company_detail_smtp_host' => 'smtp.googlemail.com',
                'company_detail_smtp_port' => '465',
                'company_detail_smtp_name' => 'App Dev',
                'company_detail_smtp_user' => 'webdev.istimewa@gmail.com',
                'company_detail_smtp_password' => 'cvychaxmbpswpsfw',
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

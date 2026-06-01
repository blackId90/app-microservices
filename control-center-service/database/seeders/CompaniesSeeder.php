<?php

namespace Database\Seeders;

use App\Enums\BillingStatusEnum;
use App\Enums\CompanyStatusEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;

class CompaniesSeeder extends Seeder {
    protected int $batchSize = 1000;

    /**
     * Run the database seeds.
     */
    public function run(): void {
        DB::table('companies')->truncate();
        foreach ($this->generateCompanies() as $chunk)
            DB::table('companies')->insert($chunk);
    }

    private function generateCompanies(): \Generator {
        $data = [
            [
                'company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_logo' => null,
                'company_name' => 'PT. Company Development',
                'company_address' => 'Jl. Company Development No. 1',
                'company_village_id' => 3673021007,
                'company_zip_code' => '55123',
                'company_fax' => null,
                'company_phone' => '081234567890',

                'company_website' => 'https://company-dev.com',
                'company_email' => 'company@dev.com',
                'company_key_email' => Str::random(200),
                'company_email_verified_at' => Carbon::now()->addMinutes(2)->format('Y-m-d H:i:s.u'),
                'company_is_status' => CompanyStatusEnum::ACTIVE,
                'company_base_price' => 100000.00,
                'company_billing_cycle' => 1,
                'company_billing_status' => BillingStatusEnum::PAID,
                'company_trial_ends_at' => Carbon::now()->addDays(7)->format('Y-m-d H:i:s.u'),
                'company_paid_ends_at' => Carbon::now()->addMonths(1)->format('Y-m-d H:i:s.u'),
            ],
            [
                'company_id' => '019b28a6-a083-73e5-b63c-070a398d9fae',
                'company_logo' => null,
                'company_name' => 'PT. Dummy Company Development 01',
                'company_address' => 'Jl. Dummy Company Development 1',
                'company_village_id' => 5207012001,
                'company_zip_code' => '55121',
                'company_fax' => null,
                'company_phone' => '081234567801',

                'company_website' => 'https://dummy-company-development01.com',
                'company_email' => 'dummy1@company.com',
                'company_key_email' => Str::random(200),
                'company_email_verified_at' => null,
                'company_is_status' => CompanyStatusEnum::PENDING,
                'company_base_price' => 50000.00,
                'company_billing_cycle' => 1,
                'company_billing_status' => BillingStatusEnum::TRIAL,
                'company_trial_ends_at' => Carbon::now()->addDays(14)->format('Y-m-d H:i:s.u'),
                'company_paid_ends_at' => null,
            ],
            [
                'company_id' => '019b28a6-a084-71c1-85e5-4d13bee2dc21',
                'company_logo' => null,
                'company_name' => 'PT. Dummy Company Development 02',
                'company_address' => 'Jl. Dummy Company Development 2',
                'company_village_id' => 5207012001,
                'company_zip_code' => '55122',
                'company_fax' => null,
                'company_phone' => '081234567802',

                'company_website' => 'https://dummy-company-development02.com',
                'company_email' => 'dummy2@company.com',
                'company_key_email' => Str::random(200),
                'company_email_verified_at' => null,
                'company_is_status' => CompanyStatusEnum::PENDING,
                'company_base_price' => 60000.00,
                'company_billing_cycle' => 1,
                'company_billing_status' => BillingStatusEnum::TRIAL,
                'company_trial_ends_at' => Carbon::now()->addDays(14)->format('Y-m-d H:i:s.u'),
                'company_paid_ends_at' => null,
            ],
            [
                'company_id' => '019b28a6-a085-72f0-9b5a-1be5dc4d9631',
                'company_logo' => null,
                'company_name' => 'PT. Dummy Company Development 03',
                'company_address' => 'Jl. Dummy Company Development 3',
                'company_village_id' => 5207012001,
                'company_zip_code' => '55124',
                'company_fax' => null,
                'company_phone' => '081234567803',

                'company_website' => 'https://dummy-company-development03.com',
                'company_email' => 'dummy3@company.com',
                'company_key_email' => Str::random(200),
                'company_email_verified_at' => null,
                'company_is_status' => CompanyStatusEnum::PENDING,
                'company_base_price' => 70000.00,
                'company_billing_cycle' => 1,
                'company_billing_status' => BillingStatusEnum::TRIAL,
                'company_trial_ends_at' => Carbon::now()->addDays(14)->format('Y-m-d H:i:s.u'),
                'company_paid_ends_at' => null,
            ],
            [
                'company_id' => '019b28a6-a085-72f0-9b5a-1be5dce183fb',
                'company_logo' => null,
                'company_name' => 'PT. Dummy Company Development 04',
                'company_address' => 'Jl. Dummy 4',
                'company_village_id' => 5207012001,
                'company_zip_code' => '55124',
                'company_fax' => null,
                'company_phone' => '081234567804',

                'company_website' => 'https://dummy-company-development03.com',
                'company_email' => 'dummy4@company.com',
                'company_key_email' => Str::random(200),
                'company_email_verified_at' => null,
                'company_is_status' => CompanyStatusEnum::PENDING,
                'company_base_price' => 70000.00,
                'company_billing_cycle' => 1,
                'company_billing_status' => BillingStatusEnum::TRIAL,
                'company_trial_ends_at' => Carbon::now()->addDays(14)->format('Y-m-d H:i:s.u'),
                'company_paid_ends_at' => null,
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

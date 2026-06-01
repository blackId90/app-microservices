<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void {
        $this->call([
            RegProvincesSeeder::class,
            RegRegenciesSeeder::class,
            RegDistrictsSeeder::class,
            RegVillagesSeeder::class,
            PlansSeeder::class,
            UsersSeeder::class,
            CompaniesSeeder::class,
            CompanyDetailsSeeder::class,
            CompanyAppAuthenticationsSeeder::class,
            CompanyInvoicesSeeder::class,
            CompanyEventsSeeder::class,
        ]);
    }
}

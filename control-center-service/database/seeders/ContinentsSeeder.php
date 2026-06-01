<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class ContinentsSeeder extends Seeder {
    // use HasUuids;

    /**
     * Run the database seeds.
     */
    public function run(): void {
        DB::table('continents')->truncate();

        foreach ($this->generateContinents() as $chunk) {
            DB::table('continents')->insert($chunk);
        }
    }

    private function generateContinents(): \Generator {
        $data = [
            [
                'continent_id' => '019e2be1-3fa5-73d4-8143-faf8c95c4991',
                'continent_code' => 'AF',
                'continent_name' => 'Africa',
                'created_at' => '2026-05-15 13:44:02.726342',
                'updated_at' => '2026-05-15 13:44:02.726342',
                'deleted_at' => NULL,
            ],
            [
                'continent_id' => '019e2be1-3fa6-703b-86f1-3184c285ef04',
                'continent_code' => 'AN',
                'continent_name' => 'Antarctica',
                'created_at' => '2026-05-15 13:44:02.726392',
                'updated_at' => '2026-05-15 13:44:02.726392',
                'deleted_at' => NULL,
            ],
            [
                'continent_id' => '019e2be1-3fa6-703b-86f1-3184c28d603b',
                'continent_code' => 'AS',
                'continent_name' => 'Asia',
                'created_at' => '2026-05-15 13:44:02.726403',
                'updated_at' => '2026-05-15 13:44:02.726403',
                'deleted_at' => NULL,
            ],
            [
                'continent_id' => '019e2be1-3fa6-703b-86f1-3184c3359726',
                'continent_code' => 'EU',
                'continent_name' => 'Europe',
                'created_at' => '2026-05-15 13:44:02.726412',
                'updated_at' => '2026-05-15 13:44:02.726412',
                'deleted_at' => NULL,
            ],
            [
                'continent_id' => '019e2be1-3fa6-703b-86f1-3184c3928692',
                'continent_code' => 'OC',
                'continent_name' => 'Australia',
                'created_at' => '2026-05-15 13:44:02.726419',
                'updated_at' => '2026-05-15 13:44:02.726419',
                'deleted_at' => NULL,
            ],
            [
                'continent_id' => '019e2be1-3fa6-703b-86f1-3184c40ad233',
                'continent_code' => 'NA',
                'continent_name' => 'North America',
                'created_at' => '2026-05-15 13:44:02.726427',
                'updated_at' => '2026-05-15 13:44:02.726427',
                'deleted_at' => NULL,
            ],
            [
                'continent_id' => '019e2be1-3fa6-703b-86f1-3184c4ecedc1',
                'continent_code' => 'SA',
                'continent_name' => 'South America',
                'created_at' => '2026-05-15 13:44:02.726434',
                'updated_at' => '2026-05-15 13:44:02.726434',
                'deleted_at' => NULL,
            ],
        ];

        $collection = LazyCollection::make($data);

        $batch = [];
        foreach ($collection as $item) {
            // $timestamp = Carbon::now();
            // $item['created_at'] = $timestamp->format('Y-m-d H:i:s.u');
            // $item['updated_at'] = $timestamp->format('Y-m-d H:i:s.u');

            $batch[] = $item;
            if (count($batch) === 1000) {
                yield $batch;
                $batch = [];
            }
        }

        if ($batch)
            yield $batch;
    }
}

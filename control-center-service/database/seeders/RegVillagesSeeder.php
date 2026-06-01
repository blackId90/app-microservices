<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class RegVillagesSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        // Tingkatkan memory limit jadi 512Mb
        ini_set('memory_limit', '256M');

        // Tingkatkan max execution time jadi 5 menit
        // ini_set('max_execution_time', '300');

        DB::table('reg_villages')->truncate();
        foreach ($this->generateRegVillages() as $chunk)
            DB::table('reg_villages')->insert($chunk);
    }

    public function generateRegVillages() {
        $jsonPath = database_path('data/reg_villages.json');
        if (!file_exists($jsonPath))
            throw new \RuntimeException("File JSON tidak ditemukan: {$jsonPath}");

        //* LazyCollection untuk stream JSON
        $collection = LazyCollection::make(function () use ($jsonPath) {
            $data = json_decode(file_get_contents($jsonPath), true);
            if ($data === null)
                throw new \RuntimeException("File JSON tidak valid: " . json_last_error_msg());

            foreach ($data as $row)
                yield $row;
        });

        $batch = [];
        foreach ($collection as $item) {
            $timestamp = Carbon::now();
            $item['created_at'] = $timestamp->format('Y-m-d H:i:s.u');
            $item['updated_at'] = $timestamp->format('Y-m-d H:i:s.u');

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

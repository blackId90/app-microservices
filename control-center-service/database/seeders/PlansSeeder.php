<?php

namespace Database\Seeders;

use App\Enums\PlanTypeEnum;
// use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class PlansSeeder extends Seeder {
    // use HasUuids;

    /**
     * Run the database seeds.
     */
    public function run(): void {
        DB::table('plans')->truncate();
        foreach ($this->generatePlans() as $chunk)
            DB::table('plans')->insert($chunk);
    }

    private function generatePlans(): \Generator {
        $data = [
            [
                'plan_id' => '019b2b6c-396f-729e-9cb1-75983823ee2d', // $this->newUniqueId()
                'plan_name' => PlanTypeEnum::DEVELOPMENT,
                'plan_description' => 'Paket Mode Development',
                'plan_is_active' => true,
            ],
            [
                'plan_id' => '019b2b6c-3970-7077-b4cb-8a535f9ee927', // $this->newUniqueId(),
                'plan_name' => PlanTypeEnum::BASIC,
                'plan_description' => 'Paket Basic',
                'plan_is_active' => true,
            ],
            [
                'plan_id' => '019b2b6c-3970-7077-b4cb-8a5360356676', // $this->newUniqueId(),
                'plan_name' => PlanTypeEnum::PRO,
                'plan_description' => 'Paket Pro',
                'plan_is_active' => true,
            ],
            [
                'plan_id' => '019b2b6c-3970-7077-b4cb-8a5360cc856a', // $this->newUniqueId(),
                'plan_name' => PlanTypeEnum::ENTERPRISE,
                'plan_description' => 'Paket Enterprise',
                'plan_is_active' => true,
            ],
        ];

        $collection = LazyCollection::make($data);

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

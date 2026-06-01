<?php

namespace Database\Seeders;

use App\Enums\UserGenderEnum;
// use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Seeder;
use Illuminate\Support\{Carbon, LazyCollection};
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder {
    // use HasUuids;

    /**
     * Run the database seeds.
     */
    public function run(): void {
        DB::table('users')->truncate();

        foreach ($this->generateUsers() as $chunk) {
            DB::table('users')->insert($chunk);
        }
    }

    private function generateUsers(): \Generator {
        $data = [
            [
                // 'user_id' => $this->newUniqueId(),
                'user_id' => '019df249-359c-729f-8bc9-b4603ab3acfb',
                'user_auth_user_id' => '019e10f5-7200-73ef-9d49-1c6d0010ec50',
                'user_avatar' => null,
                'user_first_name' => 'Super',
                'user_last_name' => 'Development',
                'user_gender' => UserGenderEnum::MALE,
                'user_address' => 'Jl. Development No. 000',
                'user_village_id' => 1810022012,
                'user_zip_code' => '12345',
                'user_phone' => '08122797880'
            ],
            [
                // 'user_id' => $this->newUniqueId(),
                'user_id' => '019df249-359c-729f-8bc9-b4603b375f1a',
                'user_auth_user_id' => '019e10f5-73fe-73ad-96b7-78934f918bd9',
                'user_avatar' => null,
                'user_first_name' => 'Super',
                'user_last_name' => 'Administrator Control Center',
                'user_gender' => UserGenderEnum::MALE,
                'user_address' => 'Jl. Administrator Control Center Development No. 001',
                'user_village_id' => 1810022012,
                'user_zip_code' => '12345',
                'user_phone' => '08122797885'
            ],
            [
                // 'user_id' => $this->newUniqueId(),
                'user_id' => '019df249-359c-729f-8bc9-b4603bafa9de',
                'user_auth_user_id' => '019e10f5-75f8-7157-babf-db2f46f8701a',
                'user_avatar' => null,
                'user_first_name' => 'Admin',
                'user_last_name' => 'Tenant Development 01',
                'user_gender' => UserGenderEnum::MALE,
                'user_address' => 'Jl. Tenant Development No. 001',
                'user_village_id' => 3173011001,
                'user_zip_code' => '23456',
                'user_phone' => '08122797890'
            ],
            [
                // 'user_id' => $this->newUniqueId(),
                'user_id' => '019df249-359c-729f-8bc9-b4603c3b9b4f',
                'user_auth_user_id' => '019e10f5-77ef-7206-a8ff-b36b63fbccc0',
                'user_avatar' => null,
                'user_first_name' => 'User',
                'user_last_name' => 'Tenant Development 01a',
                'user_gender' => UserGenderEnum::MALE,
                'user_address' => 'Jl. Tenant Development No. 002',
                'user_village_id' => 3173011006,
                'user_zip_code' => '34567',
                'user_phone' => '08122797891'
            ],
            [
                // 'user_id' => $this->newUniqueId(),
                'user_id' => '019df249-359c-729f-8bc9-b4603cc591de',
                'user_auth_user_id' => '019e10f5-79e9-7193-9608-0b8fd5836736',
                'user_avatar' => null,
                'user_first_name' => 'Admin',
                'user_last_name' => 'Tenant Development 02',
                'user_gender' => UserGenderEnum::FEMALE,
                'user_address' => 'Jl. Tenant Development No. 003',
                'user_village_id' => 3173011006,
                'user_zip_code' => '34568',
                'user_phone' => '08122797892'
            ],
            [
                // 'user_id' => $this->newUniqueId(),
                'user_id' => '019df249-359c-729f-8bc9-b4603cdd727a',
                'user_auth_user_id' => '019e10f5-7c47-7224-ac5e-f935d8a60849',
                'user_avatar' => null,
                'user_first_name' => 'User',
                'user_last_name' => 'Tenant Development 02a',
                'user_gender' => UserGenderEnum::FEMALE,
                'user_address' => 'Jl. Tenant Development No. 004',
                'user_village_id' => 3173011006,
                'user_zip_code' => '34569',
                'user_phone' => '08122797893'
            ],
            /*
            [
                'user_id' => $this->newUniqueId(),
                'user_id' => null,
                'user_auth_user_id' => '019e155e-746f-7343-96ed-17587d616d35',
                'user_avatar' => null,
                'user_first_name' => 'User',
                'user_last_name' => 'Tenant Development 02b',
                'user_gender' => UserGenderEnum::MALE,
                'user_address' => 'Jl. Tenant Development No. 005',
                'user_village_id' => 3173011006,
                'user_zip_code' => '34569',
                'user_phone' => '08122797894'
            ]
            */
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

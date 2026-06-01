<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\{Carbon, LazyCollection};
use Illuminate\Support\Facades\DB;

class AuthRolesSeeder extends Seeder {
    // use HasUuids;

    /**
     * Run the database seeds.
     */
    public function run(): void {
        DB::table('auth_roles')->truncate();

        /*
        $timeNow = Carbon::now();
        $data = $this->generateAuthRoles();

        foreach ($data as $key => &$item) {
            $timestamp = $timeNow->copy()->addSeconds($key)->format('Y-m-d H:i:s.u');

            $item['created_at'] = $timestamp;
            $item['updated_at'] = $timestamp;
        }

        DB::table('auth_roles')->insert($data);
        */

        foreach ($this->generateAuthRoles() as $chunk) {
            DB::table('auth_roles')->insert($chunk);
        }
    }

    private function generateAuthRoles(): \Generator {
        $data = [
            [
                'auth_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_slug' => 'super_dev',
                'auth_role_name' => 'Superadmin Development',
                'auth_role_is_active' => true
            ],
            [
                'auth_role_id' => '019e107f-d046-7227-9e54-d6f705e35448',
                'auth_role_slug' => 'super_admin',
                'auth_role_name' => 'Superadmin Control Center',
                'auth_role_is_active' => true
            ],
            [
                'auth_role_id' => '019e107f-d046-7227-9e54-d6f7064e4882',
                'auth_role_slug' => 'admin',
                'auth_role_name' => 'Admin Tenant',
                'auth_role_is_active' => true
            ],
            [
                'auth_role_id' => '019e107f-d046-7227-9e54-d6f706f463e4',
                'auth_role_slug' => 'staff',
                'auth_role_name' => 'Staff',
                'auth_role_is_active' => true
            ]
        ];

        // return $data;
        $collection = LazyCollection::make($data);

        $batch = [];
        foreach ($collection as $item) {
            $timestamp = Carbon::now();

            // $item['auth_role_id'] = $this->newUniqueId();
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

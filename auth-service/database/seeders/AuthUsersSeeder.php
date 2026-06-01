<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\{Carbon, LazyCollection, Str};
use Illuminate\Support\Facades\{DB, Hash};

class AuthUsersSeeder extends Seeder {
    // use HasUuids;

    /**
     * Run the database seeds.
     */
    public function run(): void {
        DB::table('auth_users')->truncate();

        /*
        $timeNow = Carbon::now();
        $data = $this->generateAuthUsers();

        foreach ($data as $key => &$item) {
            $timestamp = $timeNow->copy()->addSeconds($key)->format('Y-m-d H:i:s.u');

            $item['auth_user_password'] = Hash::make($item['auth_user_password']);
            $item['auth_user_key_email'] = Str::random(200);
            $item['auth_user_email_verified_at'] = $item['auth_user_is_admin'] === 1 ? $timestamp : null;

            $item['created_at'] = $timestamp;
            $item['updated_at'] = $timestamp;
        }

        DB::table('auth_users')->insert($data);
        */

        foreach ($this->generateAuthUsers() as $chunk) {
            DB::table('auth_users')->insert($chunk);
        }
    }

    private function generateAuthUsers(): \Generator {
        $data = [
            [
                'auth_user_id' => '019e10f5-7200-73ef-9d49-1c6d0010ec50',
                'auth_user_company_id' => NULL,
                'auth_user_email' => 'super_dev@getnada.com',
                'auth_user_username' => 'super_dev',
                'auth_user_password' => 'superdev',
                'auth_user_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_user_is_admin' => true,
                'auth_user_is_status' => '1'
            ],
            [
                'auth_user_id' => '019e10f5-73fe-73ad-96b7-78934f918bd9',
                'auth_user_company_id' => NULL,
                'auth_user_email' => 'super_admin@getnada.com',
                'auth_user_username' => 'super_administrator',
                'auth_user_password' => 'administrator',
                'auth_user_role_id' => '019e107f-d046-7227-9e54-d6f705e35448',
                'auth_user_is_admin' => true,
                'auth_user_is_status' => '1'
            ],
            [
                'auth_user_id' => '019e10f5-75f8-7157-babf-db2f46f8701a',
                'auth_user_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'auth_user_email' => 'admin_tenant01@getnada.com',
                'auth_user_username' => 'admin_tenant01',
                'auth_user_password' => '123456789',
                'auth_user_role_id' => '019e107f-d046-7227-9e54-d6f7064e4882',
                'auth_user_is_admin' => false,
                'auth_user_is_status' => '-1'
            ],
            [
                'auth_user_id' => '019e10f5-77ef-7206-a8ff-b36b63fbccc0',
                'auth_user_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'auth_user_email' => 'user_tenant01a@getnada.com',
                'auth_user_username' => 'user_tenant01a',
                'auth_user_password' => '123456789',
                'auth_user_role_id' => '019e107f-d046-7227-9e54-d6f706f463e4',
                'auth_user_is_admin' => false,
                'auth_user_is_status' => '-1'
            ],
            [
                'auth_user_id' => '019e10f5-79e9-7193-9608-0b8fd5836736',
                'auth_user_company_id' => '019b28a6-a083-73e5-b63c-070a398d9fae',
                'auth_user_email' => 'admin_tenant02@getnada.com',
                'auth_user_username' => 'admin_tenant02',
                'auth_user_password' => '123456789',
                'auth_user_role_id' => '019e107f-d046-7227-9e54-d6f7064e4882',
                'auth_user_is_admin' => false,
                'auth_user_is_status' => '-1'
            ],
            [
                'auth_user_id' => '019e10f5-7c47-7224-ac5e-f935d8a60849',
                'auth_user_company_id' => '019b28a6-a083-73e5-b63c-070a398d9fae',
                'auth_user_email' => 'user_tenant02a@getnada.com',
                'auth_user_username' => 'user_tenant02a',
                'auth_user_password' => '123456789',
                'auth_user_role_id' => '019e107f-d046-7227-9e54-d6f706f463e4',
                'auth_user_is_admin' => false,
                'auth_user_is_status' => '-1'
            ],
            [
                'auth_user_id' => '019e10f5-7f61-722b-807c-b391e125e27f',
                'auth_user_company_id' => '019b28a6-a083-73e5-b63c-070a398d9fae',
                'auth_user_email' => 'user_tenant02b@getnada.com',
                'auth_user_username' => 'user_tenant02b',
                'auth_user_password' => '123456789',
                'auth_user_role_id' => '019e107f-d046-7227-9e54-d6f706f463e4',
                'auth_user_is_admin' => false,
                'auth_user_is_status' => '-1'
            ],
        ];

        $collection = LazyCollection::make($data);

        $batch = [];
        foreach ($collection as $item) {
            $timestamp = Carbon::now();

            // $item['auth_user_id'] = $this->newUniqueId();
            $item['auth_user_password'] = Hash::make($item['auth_user_password']);
            $item['auth_user_key_email'] = Str::random(200);
            $item['auth_user_email_verified_at'] = $item['auth_user_is_admin'] ? $timestamp->format('Y-m-d H:i:s.u') : null;
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

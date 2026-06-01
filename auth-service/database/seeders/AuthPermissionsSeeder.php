<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\{Carbon, LazyCollection};
use Illuminate\Support\Facades\DB;

class AuthPermissionsSeeder extends Seeder {
    // use HasUuids;

    /**
     * Run the database seeds.
     */
    public function run(): void {
        DB::table('auth_permissions')->truncate();

        /*
        $timeNow = Carbon::now();
        $data = $this->generateAuthPermissions();

        foreach ($data as $key => &$item) {
            $timestamp = $timeNow->copy()->addSeconds($key)->format('Y-m-d H:i:s.u');

            $item['auth_permission_id'] = $this->newUniqueId();
            $item['created_at'] = $timestamp;
            $item['updated_at'] = $timestamp;
        }

        DB::table('auth_permissions')->insert($data);
        */

        foreach ($this->generateAuthPermissions() as $chunk) {
            DB::table('auth_permissions')->insert($chunk);
        }
    }

    private function generateAuthPermissions(): \Generator {
        $data = [
            //* 1. Dashboard / Home
            [
                'auth_permission_id' => '019e1031-3f3d-73da-9fca-3a076931d9dc',
                'auth_permission_type' => 'parent',
                'auth_permission_parent_permission_id' => null,
                'auth_permission_slug' => 'module.dashboard',
                'auth_permission_title' => 'Dashboard',
                'auth_permission_icon' => 'c-blue-500 ti-dashboard',
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'dashboard',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 1
            ],

            //* 2. App Setting Groups
            [
                'auth_permission_id' => '019e1031-3f3f-7296-9bb0-4c63612921a4',
                'auth_permission_type' => 'group',
                'auth_permission_parent_permission_id' => null,
                'auth_permission_slug' => 'module.app_settings',
                'auth_permission_title' => 'App Settings',
                'auth_permission_icon' => 'c-blue-500 ti-settings',
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => null,
                'auth_permission_target' => '_self',
                'auth_permission_order' => 2
            ],

            //* 2.1. Module Permissions
            [
                'auth_permission_id' => '019e1031-3f3f-7296-9bb0-4c6361b87e38',
                'auth_permission_type' => 'parent',
                'auth_permission_parent_permission_id' => '019e1031-3f3f-7296-9bb0-4c63612921a4',
                'auth_permission_slug' => 'module.auth_permissions',
                'auth_permission_title' => 'Permissions',
                'auth_permission_icon' => 'c-blue-500 ti-shield',
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'auth_permissions',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 1
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe654d48c93',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f3f-7296-9bb0-4c6361b87e38',
                'auth_permission_slug' => 'browse.auth_permissions',
                'auth_permission_title' => 'List Permissions',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'auth_permissions',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 1
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe654d982dc',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f3f-7296-9bb0-4c6361b87e38',
                'auth_permission_slug' => 'create.auth_permissions',
                'auth_permission_title' => 'Create Permissions',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'auth_permissions',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 2
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe6555c9017',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f3f-7296-9bb0-4c6361b87e38',
                'auth_permission_slug' => 'read.auth_permissions',
                'auth_permission_title' => 'Read Permissions',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'auth_permissions',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 3
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe656174d83',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f3f-7296-9bb0-4c6361b87e38',
                'auth_permission_slug' => 'update.auth_permissions',
                'auth_permission_title' => 'Update Permissions',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'auth_permissions',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 4
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe656aec519',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f3f-7296-9bb0-4c6361b87e38',
                'auth_permission_slug' => 'delete.auth_permissions',
                'auth_permission_title' => 'Delete Permissions',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'auth_permissions',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 5
            ],

            //* 2.2. Module Auth Roles
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe6577fb6d7',
                'auth_permission_type' => 'parent',
                'auth_permission_parent_permission_id' => '019e1031-3f3f-7296-9bb0-4c63612921a4',
                'auth_permission_slug' => 'module.auth_roles',
                'auth_permission_title' => 'Roles',
                'auth_permission_icon' => 'c-blue-500 ti-panel',
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'auth_roles',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 2
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe657a78f6e',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe6577fb6d7',
                'auth_permission_slug' => 'browse.auth_roles',
                'auth_permission_title' => 'List Roles',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'auth_roles',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 1
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe65823b56c',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe6577fb6d7',
                'auth_permission_slug' => 'create.auth_roles',
                'auth_permission_title' => 'Create Roles',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'auth_roles',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 2
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe65830e34f',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe6577fb6d7',
                'auth_permission_slug' => 'read.auth_roles',
                'auth_permission_title' => 'Read Roles',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'auth_roles',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 3
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe658987217',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe6577fb6d7',
                'auth_permission_slug' => 'update.auth_roles',
                'auth_permission_title' => 'Update Roles',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'auth_roles',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 4
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe6592ff489',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe6577fb6d7',
                'auth_permission_slug' => 'delete.auth_roles',
                'auth_permission_title' => 'Delete Roles',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'auth_roles',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 5
            ],

            //* 2.3. Module Company
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe65955afd6',
                'auth_permission_type' => 'parent',
                'auth_permission_parent_permission_id' => '019e1031-3f3f-7296-9bb0-4c63612921a4',
                'auth_permission_slug' => 'module.companies',
                'auth_permission_title' => 'Companies',
                'auth_permission_icon' => 'c-blue-500 ti-id-badge',
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'companies',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 3
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe6598ed117',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe65955afd6',
                'auth_permission_slug' => 'browse.companies',
                'auth_permission_title' => 'List Companies',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'companies',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 1
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe65a84ff70',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe65955afd6',
                'auth_permission_slug' => 'create.companies',
                'auth_permission_title' => 'Create Companies',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'companies',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 2
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe65b2bcea6',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe65955afd6',
                'auth_permission_slug' => 'read.companies',
                'auth_permission_title' => 'Read Companies',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'companies',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 3
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe65b75b839',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe65955afd6',
                'auth_permission_slug' => 'update.companies',
                'auth_permission_title' => 'Update Companies',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'companies',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 4,
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe65c24cd2d',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe65955afd6',
                'auth_permission_slug' => 'delete.companies',
                'auth_permission_title' => 'Delete Companies',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'companies',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 5,
            ],

            //* 2.4. Module Auth Users
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe65cc3ff3e',
                'auth_permission_type' => 'parent',
                'auth_permission_parent_permission_id' => '019e1031-3f3f-7296-9bb0-4c63612921a4',
                'auth_permission_slug' => 'module.users',
                'auth_permission_title' => 'Users',
                'auth_permission_icon' => 'c-blue-500 ti-user',
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'users',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 4
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe65d46cdac',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe65cc3ff3e',
                'auth_permission_slug' => 'browse.users',
                'auth_permission_title' => 'List Users',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'users',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 1
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe65e375839',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe65cc3ff3e',
                'auth_permission_slug' => 'create.users',
                'auth_permission_title' => 'Create Users',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'users',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 2
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe65e7ab2c4',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe65cc3ff3e',
                'auth_permission_slug' => 'read.users',
                'auth_permission_title' => 'Read Users',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'users',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 3
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe65edff94a',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe65cc3ff3e',
                'auth_permission_slug' => 'update.users',
                'auth_permission_title' => 'Update Users',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'users',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 4
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe65fc09e13',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe65cc3ff3e',
                'auth_permission_slug' => 'delete.users',
                'auth_permission_title' => 'Delete Users',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'users',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 5
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe65fcd93be',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe65cc3ff3e',
                'auth_permission_slug' => 'import.users',
                'auth_permission_title' => 'Import Users',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'users',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 6
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe65fdeaf8d',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe65cc3ff3e',
                'auth_permission_slug' => 'export.users',
                'auth_permission_title' => 'Export Users',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'users',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 7
            ],

            //* 2.5. Module Token Managements
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe66052813a',
                'auth_permission_type' => 'parent',
                'auth_permission_parent_permission_id' => '019e1031-3f3f-7296-9bb0-4c63612921a4',
                'auth_permission_slug' => 'module.token_managements',
                'auth_permission_title' => 'Token Managements',
                'auth_permission_icon' => 'c-blue-500 ti-harddrive',
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'token_managements',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 5
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe660e984b7',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe66052813a',
                'auth_permission_slug' => 'browse.token_managements',
                'auth_permission_title' => 'List Token Managements',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'token_managements',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 1
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe66136ffcf',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe66052813a',
                'auth_permission_slug' => 'create.token_managements',
                'auth_permission_title' => 'Create Token Managements',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'token_managements',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 2
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe6622d803e',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe66052813a',
                'auth_permission_slug' => 'read.token_managements',
                'auth_permission_title' => 'Read Token Managements',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'token_managements',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 3
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe6623f21a7',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe66052813a',
                'auth_permission_slug' => 'delete.token_managements',
                'auth_permission_title' => 'Delete Token Managements',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'token_managements',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 4
            ],

            //* 2.6. Module Login Attempts
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe662576488',
                'auth_permission_type' => 'parent',
                'auth_permission_parent_permission_id' => '019e1031-3f3f-7296-9bb0-4c63612921a4',
                'auth_permission_slug' => 'module.login_attempts',
                'auth_permission_title' => 'Login Attempts',
                'auth_permission_icon' => 'c-blue-500 ti-harddrive',
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'login_attempts',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 6
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe66301261f',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe662576488',
                'auth_permission_slug' => 'browse.login_attempts',
                'auth_permission_title' => 'List Login Attempts',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'login_attempts',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 1
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe663174eca',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe662576488',
                'auth_permission_slug' => 'create.login_attempts',
                'auth_permission_title' => 'Create Login Attempts',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'login_attempts',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 2
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe664118ed2',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe662576488',
                'auth_permission_slug' => 'read.login_attempts',
                'auth_permission_title' => 'Read Login Attempts',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'login_attempts',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 3
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe66413bc74',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe662576488',
                'auth_permission_slug' => 'update.login_attempts',
                'auth_permission_title' => 'Update Login Attempts',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'login_attempts',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 4
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe664cf37d0',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe662576488',
                'auth_permission_slug' => 'delete.login_attempts',
                'auth_permission_title' => 'Delete Login Attempts',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'login_attempts',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 5
            ],

            //* 2.7. Module Log Activities
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe66554d9bd',
                'auth_permission_type' => 'parent',
                'auth_permission_parent_permission_id' => '019e1031-3f3f-7296-9bb0-4c63612921a4',
                'auth_permission_slug' => 'module.log_activities',
                'auth_permission_title' => 'Log Activities',
                'auth_permission_icon' => 'c-blue-500 ti-control-record',
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'log_activities',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 7
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe665d8499f',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe66554d9bd',
                'auth_permission_slug' => 'browse.log_activities',
                'auth_permission_title' => 'List Log Activities',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'log_activities',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 1
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe66638aecf',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe66554d9bd',
                'auth_permission_slug' => 'create.log_activities',
                'auth_permission_title' => 'Create Log Activities',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'log_activities',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 2
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe66669cd0d',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe66554d9bd',
                'auth_permission_slug' => 'read.log_activities',
                'auth_permission_title' => 'Read Log Activities',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'log_activities',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 3
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe6671a1c23',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe66554d9bd',
                'auth_permission_slug' => 'update.log_activities',
                'auth_permission_title' => 'Update Log Activities',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'log_activities',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 4
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe667d44215',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe66554d9bd',
                'auth_permission_slug' => 'delete.log_activities',
                'auth_permission_title' => 'Delete Log Activities',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'log_activities',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 5
            ],

            //* 2.8. Module Continents
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe668cdf88f',
                'auth_permission_type' => 'parent',
                'auth_permission_parent_permission_id' => '019e1031-3f3f-7296-9bb0-4c63612921a4',
                'auth_permission_slug' => 'module.continents',
                'auth_permission_title' => 'Continents',
                'auth_permission_icon' => 'c-blue-500 ti-world',
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'continents',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 8
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe6690448f4',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe668cdf88f',
                'auth_permission_slug' => 'browse.continents',
                'auth_permission_title' => 'List Continents',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'continents',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 1
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe66914ea3d',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe668cdf88f',
                'auth_permission_slug' => 'create.continents',
                'auth_permission_title' => 'Create Continents',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'continents',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 2
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe6699ecb6f',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe668cdf88f',
                'auth_permission_slug' => 'read.continents',
                'auth_permission_title' => 'Read Continents',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'continents',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 3
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe66a230107',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe668cdf88f',
                'auth_permission_slug' => 'update.continents',
                'auth_permission_title' => 'Update Continents',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'continents',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 4
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe66aa86997',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe668cdf88f',
                'auth_permission_slug' => 'delete.continents',
                'auth_permission_title' => 'Delete Continents',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'continents',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 5
            ],

            //* 2.9. Module Currencies
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe66b47188e',
                'auth_permission_type' => 'parent',
                'auth_permission_parent_permission_id' => '019e1031-3f3f-7296-9bb0-4c63612921a4',
                'auth_permission_slug' => 'module.currencies',
                'auth_permission_title' => 'Currencies',
                'auth_permission_icon' => 'c-blue-500 ti-world',
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'currencies',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 9
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe66c23ab87',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe66b47188e',
                'auth_permission_slug' => 'browse.currencies',
                'auth_permission_title' => 'List Currencies',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'currencies',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 1
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe66cfa327c',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe66b47188e',
                'auth_permission_slug' => 'create.currencies',
                'auth_permission_title' => 'Create Currencies',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'currencies',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 2
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe66d0c923e',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe66b47188e',
                'auth_permission_slug' => 'read.currencies',
                'auth_permission_title' => 'Read Currencies',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'currencies',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 3
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe66d2f4c5a',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe66b47188e',
                'auth_permission_slug' => 'update.currencies',
                'auth_permission_title' => 'Update Currencies',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'currencies',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 4
            ],
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe66d856718',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe66b47188e',
                'auth_permission_slug' => 'delete.currencies',
                'auth_permission_title' => 'Delete Currencies',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'currencies',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 5
            ],

            //* 2.10. Module Currency Rates
            [
                'auth_permission_id' => '019e1031-3f40-733b-9f56-6fe66db1c302',
                'auth_permission_type' => 'parent',
                'auth_permission_parent_permission_id' => '019e1031-3f3f-7296-9bb0-4c63612921a4',
                'auth_permission_slug' => 'module.currency_rates',
                'auth_permission_title' => 'Currency Rates',
                'auth_permission_icon' => 'c-blue-500 ti-world',
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'currency_rates',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 10
            ],
            [
                'auth_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b745507b0',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe66db1c302',
                'auth_permission_slug' => 'browse.currency_rates',
                'auth_permission_title' => 'List Currency Rates',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'currency_rates',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 1
            ],
            [
                'auth_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b745513dd',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe66db1c302',
                'auth_permission_slug' => 'create.currency_rates',
                'auth_permission_title' => 'Create Currency Rates',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'currency_rates',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 2
            ],
            [
                'auth_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b752c7304',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe66db1c302',
                'auth_permission_slug' => 'read.currency_rates',
                'auth_permission_title' => 'Read Currency Rates',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'currency_rates',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 3
            ],
            [
                'auth_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b76041b1c',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe66db1c302',
                'auth_permission_slug' => 'update.currency_rates',
                'auth_permission_title' => 'Update Currency Rates',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'currency_rates',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 4
            ],
            [
                'auth_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b76272a0f',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f40-733b-9f56-6fe66db1c302',
                'auth_permission_slug' => 'delete.currency_rates',
                'auth_permission_title' => 'Delete Currency Rates',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'currency_rates',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 5
            ],

            //* 2.11. Module Countries
            [
                'auth_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b768d3f4e',
                'auth_permission_type' => 'parent',
                'auth_permission_parent_permission_id' => '019e1031-3f3f-7296-9bb0-4c63612921a4',
                'auth_permission_slug' => 'module.countries',
                'auth_permission_title' => 'Countries',
                'auth_permission_icon' => 'c-blue-500 ti-world',
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'countries',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 11
            ],
            [
                'auth_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b770490b4',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b768d3f4e',
                'auth_permission_slug' => 'browse.countries',
                'auth_permission_title' => 'List Countries',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'countries',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 1
            ],
            [
                'auth_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b77adc532',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b768d3f4e',
                'auth_permission_slug' => 'create.countries',
                'auth_permission_title' => 'Create Countries',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'countries',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 2
            ],
            [
                'auth_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b78456af6',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b768d3f4e',
                'auth_permission_slug' => 'read.countries',
                'auth_permission_title' => 'Read Countries',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'countries',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 3
            ],
            [
                'auth_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b7845b50c',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b768d3f4e',
                'auth_permission_slug' => 'update.countries',
                'auth_permission_title' => 'Update Countries',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'countries',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 4
            ],
            [
                'auth_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b791c2f0b',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b768d3f4e',
                'auth_permission_slug' => 'delete.countries',
                'auth_permission_title' => 'Delete Countries',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'countries',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 5
            ],

            //* 2.12. Module Languages
            [
                'auth_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b79c02a57',
                'auth_permission_type' => 'parent',
                'auth_permission_parent_permission_id' => '019e1031-3f3f-7296-9bb0-4c63612921a4',
                'auth_permission_slug' => 'module.languages',
                'auth_permission_title' => 'Languages',
                'auth_permission_icon' => 'c-blue-500 ti-world',
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'languages',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 12
            ],
            [
                'auth_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b7a261b4e',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b79c02a57',
                'auth_permission_slug' => 'browse.languages',
                'auth_permission_title' => 'List Languages',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'languages',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 1
            ],
            [
                'auth_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b7ae1b59e',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b79c02a57',
                'auth_permission_slug' => 'create.languages',
                'auth_permission_title' => 'Create Languages',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'languages',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 2
            ],
            [
                'auth_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b7b11373d',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b79c02a57',
                'auth_permission_slug' => 'read.languages',
                'auth_permission_title' => 'Read Languages',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'languages',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 3
            ],
            [
                'auth_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b7c083c84',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b79c02a57',
                'auth_permission_slug' => 'update.languages',
                'auth_permission_title' => 'Update Languages',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'languages',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 4
            ],
            [
                'auth_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b7ccf1eac',
                'auth_permission_type' => 'item',
                'auth_permission_parent_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b79c02a57',
                'auth_permission_slug' => 'delete.languages',
                'auth_permission_title' => 'Delete Languages',
                'auth_permission_icon' => null,
                'auth_permission_color' => null,
                'auth_permission_url' => null,
                'auth_permission_route' => 'languages',
                'auth_permission_target' => '_self',
                'auth_permission_order' => 5
            ],
        ];

        // return $data;
        $collection = LazyCollection::make($data);

        $batch = [];
        foreach ($collection as $item) {
            $timestamp = Carbon::now();
            // $item['auth_permission_id'] = $this->newUniqueId();
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

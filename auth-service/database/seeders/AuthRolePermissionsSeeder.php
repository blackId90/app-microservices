<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class AuthRolePermissionsSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        DB::table('auth_role_permissions')->truncate();

        /*
        $data = $this->generateAuthRolePermissions();

        DB::table('auth_role_permissions')->insert($data);
        */

        foreach ($this->generateAuthRolePermissions() as $chunk) {
            DB::table('auth_role_permissions')->insert($chunk);
        }
    }

    private function generateAuthRolePermissions(): \Generator {
        $data = [
            // ** dashboard (Superadmin Development)
            [
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f3d-73da-9fca-3a076931d9dc',
                'auth_role_permission_parameter' => NULL
            ],

            // ** settings groups (Superadmin Development)
            [
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f3f-7296-9bb0-4c63612921a4',
                'auth_role_permission_parameter' => NULL
            ],

            // ** module_permissions (Superadmin Development)
            [
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f3f-7296-9bb0-4c6361b87e38',
                'auth_role_permission_parameter' => NULL
            ],
            [
                // browse_permissions
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe654d48c93',
                'auth_role_permission_parameter' => 3
            ],
            [
                // create_permissions
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe654d982dc',
                'auth_role_permission_parameter' => NULL
            ],
            [
                // read_permissions
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe6555c9017',
                'auth_role_permission_parameter' => 2
            ],
            [
                // update_permissions
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe656174d83',
                'auth_role_permission_parameter' => 2
            ],
            [
                // delete_permissions
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe656aec519',
                'auth_role_permission_parameter' => 4
            ],

            // ** module_roles (Superadmin Development)
            [
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe6577fb6d7',
                'auth_role_permission_parameter' => NULL
            ],
            [
                // browse_roles
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe657a78f6e',
                'auth_role_permission_parameter' => 3
            ],
            [
                // create_roles
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe65823b56c',
                'auth_role_permission_parameter' => NULL
            ],
            [
                // read_roles
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe65830e34f',
                'auth_role_permission_parameter' => 2
            ],
            [
                // update_roles
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe658987217',
                'auth_role_permission_parameter' => 2
            ],
            [
                // delete_roles
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe6592ff489',
                'auth_role_permission_parameter' => 4
            ],

            // ** module_company (Superadmin Development)
            [
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe65955afd6',
                'auth_role_permission_parameter' => NULL
            ],
            [
                // browse_company
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe6598ed117',
                'auth_role_permission_parameter' => 3
            ],
            [
                // create_company
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe65a84ff70',
                'auth_role_permission_parameter' => NULL
            ],
            [
                // read_company
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe65b2bcea6',
                'auth_role_permission_parameter' => 2
            ],
            [
                // update_company
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe65b75b839',
                'auth_role_permission_parameter' => 2
            ],
            [
                // delete_company
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe65c24cd2d',
                'auth_role_permission_parameter' => 4
            ],

            // ** module_users (Superadmin Development)
            [
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe65cc3ff3e',
                'auth_role_permission_parameter' => NULL
            ],
            [
                // browse_users
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe65d46cdac',
                'auth_role_permission_parameter' => 3
            ],
            [
                // create_users
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe65e375839',
                'auth_role_permission_parameter' => NULL
            ],
            [
                // read_users
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe65e7ab2c4',
                'auth_role_permission_parameter' => 2
            ],
            [
                // update_users
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe65edff94a',
                'auth_role_permission_parameter' => 2
            ],
            [
                // delete_users
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe65fc09e13',
                'auth_role_permission_parameter' => 4
            ],
            [
                // import_users
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe65fcd93be',
                'auth_role_permission_parameter' => null
            ],
            [
                // export_users
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe65fdeaf8d',
                'auth_role_permission_parameter' => 2
            ],

            // ** module_token_managements (Superadmin Development)
            [
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe66052813a',
                'auth_role_permission_parameter' => 1
            ],
            [
                // browse_token_managements
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe660e984b7',
                'auth_role_permission_parameter' => 1
            ],
            [
                // create_token_managements
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe66136ffcf',
                'auth_role_permission_parameter' => NULL
            ],
            [
                // read_token_managements
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe6622d803e',
                'auth_role_permission_parameter' => 1
            ],
            [
                // delete_token_managements
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe6623f21a7',
                'auth_role_permission_parameter' => 1
            ],

            // ** module_login_attempts (Superadmin Development)
            [
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe662576488',
                'auth_role_permission_parameter' => NULL
            ],
            [
                // browse_login_attempts
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe66301261f',
                'auth_role_permission_parameter' => 3
            ],
            [
                // create_login_attempts
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe663174eca',
                'auth_role_permission_parameter' => NULL
            ],
            [
                // read_login_attempts
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe664118ed2',
                'auth_role_permission_parameter' => 2
            ],
            [
                // update_login_attempts
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe66413bc74',
                'auth_role_permission_parameter' => 2
            ],
            [
                // delete_login_attempts
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe664cf37d0',
                'auth_role_permission_parameter' => 4
            ],

            // ** module_log_activities (Superadmin Development)
            [
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe66554d9bd',
                'auth_role_permission_parameter' => NULL
            ],
            [
                // browse_log_activities
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe665d8499f',
                'auth_role_permission_parameter' => 3
            ],
            [
                // create_log_activities
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe66638aecf',
                'auth_role_permission_parameter' => NULL
            ],
            [
                // read_log_activities
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe66669cd0d',
                'auth_role_permission_parameter' => 2
            ],
            [
                // update_log_activities
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe6671a1c23',
                'auth_role_permission_parameter' => 2
            ],
            [
                // delete_log_activities
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe667d44215',
                'auth_role_permission_parameter' => 4
            ],

            // ** module_continents (Superadmin Development)
            [
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe668cdf88f',
                'auth_role_permission_parameter' => NULL
            ],
            [
                // browse_continents
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe6690448f4',
                'auth_role_permission_parameter' => 3
            ],
            [
                // create_continents
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe66914ea3d',
                'auth_role_permission_parameter' => NULL
            ],
            [
                // read_continents
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe6699ecb6f',
                'auth_role_permission_parameter' => 2
            ],
            [
                // update_continents
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe66a230107',
                'auth_role_permission_parameter' => 2
            ],
            [
                // delete_continents
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe66aa86997',
                'auth_role_permission_parameter' => 4
            ],

            // ** module_currencies (Superadmin Development)
            [
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe66b47188e',
                'auth_role_permission_parameter' => NULL
            ],
            [
                // browse_currencies
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe66c23ab87',
                'auth_role_permission_parameter' => 3
            ],
            [
                // create_currencies
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe66cfa327c',
                'auth_role_permission_parameter' => NULL
            ],
            [
                // read_currencies
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe66d0c923e',
                'auth_role_permission_parameter' => 2
            ],
            [
                // update_currencies
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe66d2f4c5a',
                'auth_role_permission_parameter' => 2
            ],
            [
                // delete_currencies
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe66d856718',
                'auth_role_permission_parameter' => 4
            ],

            // ** module_currency_rates (Superadmin Development)
            [
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f40-733b-9f56-6fe66db1c302',
                'auth_role_permission_parameter' => NULL
            ],
            [
                // browse_currency_rates
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b745507b0',
                'auth_role_permission_parameter' => 3
            ],
            [
                // create_currency_rates
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b745513dd',
                'auth_role_permission_parameter' => NULL
            ],
            [
                // read_currency_rates
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b752c7304',
                'auth_role_permission_parameter' => 2
            ],
            [
                // update_currency_rates
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b76041b1c',
                'auth_role_permission_parameter' => 2
            ],
            [
                // delete_currency_rates
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b76272a0f',
                'auth_role_permission_parameter' => 4
            ],

            // ** module_countries (Superadmin Development)
            [
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b768d3f4e',
                'auth_role_permission_parameter' => NULL
            ],
            [
                // browse_countries
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b770490b4',
                'auth_role_permission_parameter' => 3
            ],
            [
                // create_countries
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b77adc532',
                'auth_role_permission_parameter' => NULL
            ],
            [
                // read_countries
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b78456af6',
                'auth_role_permission_parameter' => 2
            ],
            [
                // update_countries
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b7845b50c',
                'auth_role_permission_parameter' => 2
            ],
            [
                // delete_countries
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b791c2f0b',
                'auth_role_permission_parameter' => 4
            ],

            // ** module_languages (Superadmin Development)
            [
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b79c02a57',
                'auth_role_permission_parameter' => NULL
            ],
            [
                // browse_languages
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b7a261b4e',
                'auth_role_permission_parameter' => 3
            ],
            [
                // create_languages
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b7ae1b59e',
                'auth_role_permission_parameter' => NULL
            ],
            [
                // read_languages
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b7b11373d',
                'auth_role_permission_parameter' => 2
            ],
            [
                // update_languages
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b7c083c84',
                'auth_role_permission_parameter' => 2
            ],
            [
                // delete_languages
                'auth_role_permission_role_id' => '019e107f-d045-738c-8a3a-73b598bb5562',
                'auth_role_permission_permission_id' => '019e1031-3f41-73bc-9dbf-2f6b7ccf1eac',
                'auth_role_permission_parameter' => 4
            ],
        ];

        // return $data;
        $collection = LazyCollection::make($data);

        $batch = [];
        foreach ($collection as $item) {
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

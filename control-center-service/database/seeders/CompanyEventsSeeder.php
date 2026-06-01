<?php

namespace Database\Seeders;

use App\Enums\CompanyEventTypeEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class CompanyEventsSeeder extends Seeder {
    use HasUuids;

    protected int $batchSize = 1000;

    /**
     * Run the database seeds.
     */
    public function run(): void {
        DB::table('company_events')->truncate();
        foreach ($this->generateCompanyEventsSeeder() as $chunk)
            DB::table('company_events')->insert($chunk);
    }

    private function generateCompanyEventsSeeder(): \Generator {
        $data = [
            // ===========================================
            // EVENT TYPE: PROVISIONING (Tenant baru dibuat)
            // ===========================================
            [
                'company_event_id' => '019b0c44-6301-72ca-9655-afd35975a470',
                'company_event_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_event_type' => CompanyEventTypeEnum::PROVISIONING,
                'company_event_description' => 'Tenant database company_dev berhasil dibuat',
                'company_event_metadata' => json_encode([
                    'database_name' => 'company_dev_db_database',
                    'schema_name' => 'public',
                    'admin_user' => 'company_dev_username_db',
                    'connection_string' => 'company_dev_db_host.cluster-abc123.us-east-1.rds.dev.com:5432',
                    'provisioning_time' => '2024-01-15 10:30:00',
                    'resources' => ['cpu' => '2 cores', 'memory' => '4GB', 'storage' => '50GB']
                ]),
                'company_event_status' => 1, // Success
                'created_by' => 'ae2c3e17-6494-11ed-bcd1-186024b8d174',
            ],
            [
                'company_event_id' => '019b0c44-6301-72ca-9655-afd35975a471',
                'company_event_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_event_type' => CompanyEventTypeEnum::PROVISIONING,
                'company_event_description' => 'Gagal membuat tenant database karena resource tidak cukup',
                'company_event_metadata' => json_encode([
                    'attempted_database' => 'company_dev_backup',
                    'error_message' => 'Insufficient storage space available',
                    'required_storage' => '100GB',
                    'available_storage' => '45GB',
                    'attempt_timestamp' => '2024-01-14 15:45:00'
                ]),
                'company_event_status' => 0, // Failed
                'created_by' => 'ae2c3e17-6494-11ed-bcd1-186024b8d174',
            ],

            // ===========================================
            // EVENT TYPE: MIGRATION (Membuat schema database tenant)
            // ===========================================
            [
                'company_event_id' => '019b0c44-6301-72ca-9655-afd35975a472',
                'company_event_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_event_type' => CompanyEventTypeEnum::MIGRATION,
                'company_event_description' => 'Migration semua schema berhasil dijalankan',
                'company_event_metadata' => json_encode([
                    'migration_batch' => 1,
                    'total_migrations' => 25,
                    'executed_migrations' => 25,
                    'execution_time' => '00:02:45',
                    'last_migration' => '2024_01_15_000000_create_users_table',
                    'database' => 'company_dev_db_database'
                ]),
                'company_event_status' => 1, // Success
                'created_by' => 'ae2c3e17-6494-11ed-bcd1-186024b8d174',
            ],
            [
                'company_event_id' => '019b0c44-6301-72ca-9655-afd35975a473',
                'company_event_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_event_type' => CompanyEventTypeEnum::MIGRATION,
                'company_event_description' => 'Migration gagal pada tabel orders',
                'company_event_metadata' => json_encode([
                    'migration_batch' => 2,
                    'failed_migration' => '2024_01_15_000001_create_orders_table',
                    'error_message' => 'SQLSTATE[42S01]: Base table or view already exists: 1050 Table \'orders\' already exists',
                    'line_number' => 45,
                    'stack_trace' => '... truncated ...',
                    'retry_count' => 3
                ]),
                'company_event_status' => 0, // Failed
                'created_by' => 'ae2c3e17-6494-11ed-bcd1-186024b8d174',
            ],
            [
                'company_event_id' => '019b0c44-6301-72ca-9655-afd35975a474',
                'company_event_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_event_type' => CompanyEventTypeEnum::MIGRATION,
                'company_event_description' => 'Migration baru sedang dipersiapkan',
                'company_event_metadata' => json_encode([
                    'migration_batch' => 3,
                    'pending_migrations' => 5,
                    'scheduled_time' => '2024-01-16 02:00:00',
                    'estimated_duration' => '00:01:30'
                ]),
                'company_event_status' => -1, // Pending
                'created_by' => 'ae2c3e17-6494-11ed-bcd1-186024b8d174',
            ],

            // ===========================================
            // EVENT TYPE: SEED (Insert data default)
            // ===========================================
            [
                'company_event_id' => '019b0c44-6301-72ca-9655-afd35975a475',
                'company_event_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_event_type' => CompanyEventTypeEnum::SEED,
                'company_event_description' => 'Seeder data default berhasil dijalankan',
                'company_event_metadata' => json_encode([
                    'seeders_executed' => ['DatabaseSeeder', 'UsersTableSeeder', 'SettingsTableSeeder'],
                    'total_records' => 150,
                    'execution_time' => '00:00:45',
                    'tables_affected' => ['users', 'settings', 'roles', 'permissions'],
                    'database' => 'company_dev_db_database'
                ]),
                'company_event_status' => 1, // Success
                'created_by' => 'ae2c3e17-6494-11ed-bcd1-186024b8d174',
            ],
            [
                'company_event_id' => '019b0c44-6301-72ca-9655-afd35975a476',
                'company_event_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_event_type' => CompanyEventTypeEnum::SEED,
                'company_event_description' => 'Seeder gagal karena constraint violation',
                'company_event_metadata' => json_encode([
                    'failed_seeder' => 'ProductsTableSeeder',
                    'error_message' => 'SQLSTATE[23505]: Unique violation: 7 ERROR: duplicate key value violates unique constraint',
                    'duplicate_value' => 'product_code: PRD-001',
                    'table' => 'products',
                    'attempted_record' => 47
                ]),
                'company_event_status' => 0, // Failed
                'created_by' => 'ae2c3e17-6494-11ed-bcd1-186024b8d174',
            ],

            // ===========================================
            // EVENT TYPE: SUSPENSION (Tenant suspended karena expired)
            // ===========================================
            [
                'company_event_id' => '019b0c44-6301-72ca-9655-afd35975a477',
                'company_event_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_event_type' => CompanyEventTypeEnum::SUSPENSION,
                'company_event_description' => 'Tenant ditangguhkan karena pembayaran expired',
                'company_event_metadata' => json_encode([
                    'suspension_reason' => 'invoice_expired',
                    'invoice_id' => '019b0c44-6301-72ca-9655-afd35975a489',
                    'expired_date' => '2024-01-15',
                    'grace_period_days' => 7,
                    'action_taken' => 'database_connection_blocked',
                    'notify_users' => true,
                    'affected_services' => ['api', 'dashboard', 'reports']
                ]),
                'company_event_status' => 1, // Success
                'created_by' => 'ae2c3e17-6494-11ed-bcd1-186024b8d174',
            ],
            [
                'company_event_id' => '019b0c44-6301-72ca-9655-afd35975a478',
                'company_event_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_event_type' => CompanyEventTypeEnum::SUSPENSION,
                'company_event_description' => 'Proses suspension tenant sedang berjalan',
                'company_event_metadata' => json_encode([
                    'suspension_reason' => 'manual_by_admin',
                    'requested_by' => 'admin_supervisor',
                    'scheduled_time' => '2024-01-16 00:00:00',
                    'estimated_completion' => '2024-01-16 00:05:00',
                    'preparation_steps' => ['backup_data', 'notify_users', 'block_access']
                ]),
                'company_event_status' => -1, // Pending
                'created_by' => 'ae2c3e17-6494-11ed-bcd1-186024b8d174',
            ],

            // ===========================================
            // EVENT TYPE: BILLING (Pembayaran invoice)
            // ===========================================
            [
                'company_event_id' => '019b0c44-6301-72ca-9655-afd35975a479',
                'company_event_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_event_type' => 'billing',
                'company_event_description' => 'Pembayaran invoice bulanan berhasil',
                'company_event_metadata' => json_encode([
                    'invoice_id' => '019b0c44-6301-72ca-9655-afd35975a483',
                    'amount' => '299000.00',
                    'currency' => 'IDR',
                    'payment_method' => 'credit_card',
                    'transaction_id' => 'trx_987654321',
                    'payment_date' => '2024-01-15 14:30:00',
                    'months_paid' => 1,
                    'valid_until' => '2024-02-15 23:59:59'
                ]),
                'company_event_status' => 1, // Success
                'created_by' => 'ae2c3e17-6494-11ed-bcd1-186024b8d174',
            ],
            [
                'company_event_id' => '019b0c44-6301-72ca-9655-afd35975a480',
                'company_event_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_event_type' => 'billing',
                'company_event_description' => 'Pembayaran invoice gagal - kartu kredit ditolak',
                'company_event_metadata' => json_encode([
                    'invoice_id' => '019b0c44-6301-72ca-9655-afd35975a489',
                    'amount' => '897000.00',
                    'payment_method' => 'credit_card',
                    'error_message' => 'Credit card declined by issuer',
                    'decline_code' => '05',
                    'attempt_count' => 3,
                    'next_retry' => '2024-01-16 10:00:00'
                ]),
                'company_event_status' => 0, // Failed
                'created_by' => 'ae2c3e17-6494-11ed-bcd1-186024b8d174',
            ],

            // ===========================================
            // EVENT TYPE: PERMISSION_UPDATE (Admin update permission tenant)
            // ===========================================
            [
                'company_event_id' => '019b0c44-6301-72ca-9655-afd35975a481',
                'company_event_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_event_type' => 'permission_update',
                'company_event_description' => 'Update permission role administrator berhasil',
                'company_event_metadata' => json_encode([
                    'updated_by' => 'super_admin',
                    'target_user' => 'admin_company',
                    'role_changes' => [
                        'old_role' => 'company_admin',
                        'new_role' => 'company_super_admin'
                    ],
                    'permission_added' => ['manage_users', 'view_reports', 'export_data'],
                    'permission_removed' => ['modify_settings'],
                    'effective_date' => '2024-01-15 09:00:00'
                ]),
                'company_event_status' => 1, // Success
                'created_by' => 'ae2c3e17-6494-11ed-bcd1-186024b8d174',
            ],
            [
                'company_event_id' => '019b0c44-6301-72ca-9655-afd35975a482',
                'company_event_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_event_type' => 'permission_update',
                'company_event_description' => 'Permission update sedang divalidasi',
                'company_event_metadata' => json_encode([
                    'requested_by' => 'hr_manager',
                    'request_details' => 'Add payroll access for finance team',
                    'approvers' => ['it_director', 'finance_manager'],
                    'current_status' => 'awaiting_approval',
                    'requested_permissions' => ['view_payroll', 'process_payroll', 'generate_payslips']
                ]),
                'company_event_status' => -1, // Pending
                'created_by' => 'ae2c3e17-6494-11ed-bcd1-186024b8d174',
            ],
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

<?php

namespace Database\Seeders;

use App\Enums\CompanyInvoiceStatusEnum;
use App\Enums\PaymentsMethodsEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class CompanyInvoicesSeeder extends Seeder {
    use HasUuids;

    protected int $batchSize = 1000;

    /**
     * Run the database seeds.
     */
    public function run(): void {
        DB::table('company_invoices')->truncate();
        foreach ($this->generateCompanyInvoicesSeeder() as $chunk)
            DB::table('company_invoices')->insert($chunk);
    }

    private function generateCompanyInvoicesSeeder(): \Generator {
        $data = [
            // ================================
            // INVOICE PAID (SUDAH DIBAYAR)
            // ================================
            [
                'company_invoice_id' => $this->newUniqueId(),
                'company_invoice_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_invoice_no_inv' => 'INV-20260110-00000001',
                'company_invoice_amount' => 299000.00,
                'company_invoice_months_paid' => 1,
                'company_invoice_paid_at' => Carbon::now()->subDays(2)->format('Y-m-d H:i:s.u'),
                'company_invoice_valid_until' => Carbon::now()->addDays(28)->format('Y-m-d H:i:s.u'),
                'company_invoice_payment_method' => PaymentsMethodsEnum::CREDIT_CARD,
                'company_invoice_status' => CompanyInvoiceStatusEnum::PAID,
            ],
            [
                'company_invoice_id' => $this->newUniqueId(),
                'company_invoice_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_invoice_no_inv' => 'INV-20260110-00000002',
                'company_invoice_amount' => 2499000.00,
                'company_invoice_months_paid' => 12,
                'company_invoice_paid_at' => Carbon::now()->subMonths(3)->format('Y-m-d H:i:s.u'),
                'company_invoice_valid_until' => Carbon::now()->addMonths(9)->format('Y-m-d H:i:s.u'),
                'company_invoice_payment_method' => PaymentsMethodsEnum::EWALLET,
                'company_invoice_status' => CompanyInvoiceStatusEnum::PAID,
            ],
            [
                'company_invoice_id' => $this->newUniqueId(),
                'company_invoice_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_invoice_no_inv' => 'INV-20260110-00000003',
                'company_invoice_amount' => 897000.00,
                'company_invoice_months_paid' => 3,
                'company_invoice_paid_at' => Carbon::now()->subDays(15)->format('Y-m-d H:i:s.u'),
                'company_invoice_valid_until' => Carbon::now()->addMonths(2)->addDays(15)->format('Y-m-d H:i:s.u'),
                'company_invoice_payment_method' => PaymentsMethodsEnum::VIRTUAL_ACCOUNT,
                'company_invoice_status' => CompanyInvoiceStatusEnum::PAID,
            ],
            [
                'company_invoice_id' => $this->newUniqueId(),
                'company_invoice_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_invoice_no_inv' => 'INV-20260110-00000004',
                'company_invoice_amount' => 100000.00, // '1495000.00',
                'company_invoice_months_paid' => 1,
                'company_invoice_paid_at' => Carbon::now()->addMonths(1)->format('Y-m-d H:i:s.u'),
                'company_invoice_valid_until' => Carbon::now()->addMonths(1)->format('Y-m-d H:i:s.u'),
                'company_invoice_payment_method' => PaymentsMethodsEnum::BANK_TRANSFER,
                'company_invoice_status' => CompanyInvoiceStatusEnum::PAID,
            ],

            // ================================
            // INVOICE PENDING (MENUNGGU PEMBAYARAN)
            // ================================
            [
                'company_invoice_id' => $this->newUniqueId(),
                'company_invoice_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_invoice_no_inv' => 'INV-20260110-00000005',
                'company_invoice_amount' => 299000.00,
                'company_invoice_months_paid' => 1,
                'company_invoice_paid_at' => null,
                'company_invoice_valid_until' => Carbon::now()->addDays(7)->format('Y-m-d H:i:s.u'),
                'company_invoice_payment_method' => null,
                'company_invoice_status' => CompanyInvoiceStatusEnum::PENDING,
            ],
            [
                'company_invoice_id' => $this->newUniqueId(),
                'company_invoice_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_invoice_no_inv' => 'INV-20260110-00000006',
                'company_invoice_amount' => 897000.00,
                'company_invoice_months_paid' => 3,
                'company_invoice_paid_at' => null,
                'company_invoice_valid_until' => Carbon::now()->addDays(3)->format('Y-m-d H:i:s.u'),
                'company_invoice_payment_method' => null,
                'company_invoice_status' => CompanyInvoiceStatusEnum::PENDING,
            ],
            [
                'company_invoice_id' => $this->newUniqueId(),
                'company_invoice_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_invoice_no_inv' => 'INV-20260110-00000007',
                'company_invoice_amount' => 1495000.00,
                'company_invoice_months_paid' => 6,
                'company_invoice_paid_at' => null,
                'company_invoice_valid_until' => Carbon::now()->addDays(1)->format('Y-m-d H:i:s.u'), // besok habis
                'company_invoice_payment_method' => null,
                'company_invoice_status' => CompanyInvoiceStatusEnum::PENDING,
            ],
            [
                'company_invoice_id' => $this->newUniqueId(),
                'company_invoice_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_invoice_no_inv' => 'INV-20260110-00000008',
                'company_invoice_amount' => 499000.00, // paket premium bulanan
                'company_invoice_months_paid' => 1,
                'company_invoice_paid_at' => null,
                'company_invoice_valid_until' => Carbon::now()->addDays(14)->format('Y-m-d H:i:s.u'), // masih lama
                'company_invoice_payment_method' => null,
                'company_invoice_status' => CompanyInvoiceStatusEnum::PENDING,
            ],

            // ================================
            // INVOICE FAILED (GAGAL BAYAR)
            // ================================
            [
                'company_invoice_id' => $this->newUniqueId(),
                'company_invoice_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_invoice_no_inv' => 'INV-20260110-00000009',
                'company_invoice_amount' => 299000.00,
                'company_invoice_months_paid' => 1,
                'company_invoice_paid_at' => null,
                'company_invoice_valid_until' => Carbon::now()->subDays(2)->format('Y-m-d H:i:s.u'), // sudah lewat
                'company_invoice_payment_method' => PaymentsMethodsEnum::CREDIT_CARD, // pernah mencoba bayar
                'company_invoice_status' => CompanyInvoiceStatusEnum::FAILED,
            ],
            [
                'company_invoice_id' => $this->newUniqueId(),
                'company_invoice_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_invoice_no_inv' => 'INV-20260110-00000010',
                'company_invoice_amount' => 897000.00,
                'company_invoice_months_paid' => 3,
                'company_invoice_paid_at' => null,
                'company_invoice_valid_until' => Carbon::now()->subDays(10)->format('Y-m-d H:i:s.u'),
                'company_invoice_payment_method' => PaymentsMethodsEnum::BANK_TRANSFER, // pernah mencoba
                'company_invoice_status' => CompanyInvoiceStatusEnum::FAILED,
            ],
            [
                'company_invoice_id' => $this->newUniqueId(),
                'company_invoice_company_id' => '019b0c44-6301-72ca-9655-afd35975a469',
                'company_invoice_no_inv' => 'INV-20260110-00000011',
                'company_invoice_amount' => 2499000.00,
                'company_invoice_months_paid' => 12,
                'company_invoice_paid_at' => null,
                'company_invoice_valid_until' => Carbon::now()->subDays(30)->format('Y-m-d H:i:s.u'), // sudah lama lewat
                'company_invoice_payment_method' => null, // tidak pernah mencoba
                'company_invoice_status' => CompanyInvoiceStatusEnum::FAILED,
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

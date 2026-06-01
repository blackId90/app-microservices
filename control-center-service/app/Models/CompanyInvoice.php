<?php

namespace App\Models;

use App\Enums\CompanyInvoiceStatusEnum;
use App\Enums\PaymentsMethodsEnum;
use App\Traits\DatetimeFormatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyInvoice extends Model {
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasUuids, SoftDeletes, DatetimeFormatter, HasFactory;

    protected $table = 'company_invoices';
    protected $keyType = 'string';
    protected $primaryKey = 'company_invoice_id';
    protected $dateFormat = 'Y-m-d\TH:i:s.u\Z'; // ISO 8601
    public $incrementing = false;

    protected $fillable = [
        'company_invoice_company_id',
        'company_invoice_no_inv',
        'company_invoice_amount',
        'company_invoice_months_paid',
        'company_invoice_payment_method',
        'company_invoice_paid_at',
        'company_invoice_valid_until',
        'company_invoice_status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'company_invoice_amount' => 'decimal:2',
            'company_invoice_months_paid' => 'integer',
            'company_invoice_payment_method' => PaymentsMethodsEnum::class,
            'company_invoice_paid_at' => 'datetime:Y-m-d H:i:s.u',
            'company_invoice_valid_until' => 'datetime:Y-m-d H:i:s.u',
            'company_invoice_status' => CompanyInvoiceStatusEnum::class,
            'created_at' => 'datetime:Y-m-d H:i:s.u',
            'updated_at' => 'datetime:Y-m-d H:i:s.u',
            'deleted_at' => 'datetime:Y-m-d H:i:s.u',
        ];
    }

    //* Relationships
    public function company() {
        return $this->belongsTo(Company::class, 'company_invoice_company_id', 'company_id');
    }

    //* Scopes
    public function scopeLatestPaid(Builder $query, int $limit = 3): Builder {
        return $query->where('company_invoice_status', CompanyInvoiceStatusEnum::PAID)
            ->orderByDesc('created_at')
            ->limit($limit);
    }

    /*
    public function scopePaid($query) {
        return $query->where('company_invoice_status', 'paid');
    }

    public function scopePending($query) {
        return $query->where('company_invoice_status', 'pending');
    }

    public function scopeFailed($query) {
        return $query->where('company_invoice_status', 'failed');
    }

    public function scopeValid($query) {
        return $query->where('company_invoice_valid_until', '>', now());
    }

    public function scopeExpired($query) {
        return $query->where('company_invoice_valid_until', '<=', now());
    }

    public function isPaid(): bool {
        return $this->company_invoice_status === 'paid';
    }

    public function isValid(): bool {
        return $this->company_invoice_valid_until->isFuture();
    }

    //* Methods
    public function markAsPaid($paymentMethod, $paidAt = null): bool {
        return $this->update([
            'company_invoice_status' => 'paid',
            'company_invoice_payment_method' => $paymentMethod,
            'company_invoice_paid_at' => $paidAt ?? now(),
        ]);
    }
    */
}

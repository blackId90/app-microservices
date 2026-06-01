<?php

namespace App\Models;

use App\Enums\{BillingStatusEnum, CompanyStatusEnum};
use App\Models\Concerns\{ApplyFilterPaginationScopes, ApplyFilterReadScopes, ApplyPerformDeleteAction, ApplyWithTrashedRelationScopes};
use App\Traits\DatetimeFormatter;
use Illuminate\Database\Eloquent\{Builder, Model, SoftDeletes};
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\{Carbon, Str};
use Illuminate\Support\Facades\Auth;

class Company extends Model {
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasUuids, SoftDeletes, DatetimeFormatter, HasFactory, ApplyFilterPaginationScopes, ApplyFilterReadScopes, ApplyPerformDeleteAction, ApplyWithTrashedRelationScopes;

    protected $table = 'companies';
    protected $keyType = 'string';
    protected $primaryKey = 'company_id';
    public $incrementing = false;
    protected $dateFormat = 'Y-m-d\TH:i:s.u\Z'; // ISO 8601
    protected $datetimeFormat = 'Y-m-d\TH:i:s.u\Z'; // 'Y-m-d H:i:s.u';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>
     */
    // protected $guarded = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_logo',
        'company_name',
        'company_address',
        'company_village_id',
        'company_zip_code',
        'company_fax',
        'company_phone',
        'company_website',
        'company_email',
        'company_paid_ends_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'company_village_id',
        'company_key_email'
    ];

    /**
     * The relations to eager load on every query.
     * Nested eager loading for location region
     *
     * @var array
     */
    protected $with = [
        'village.district.regency.province',
        'details'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'company_email_verified_at' => 'datetime:Y-m-d H:i:s.u',
            'company_is_status' => CompanyStatusEnum::class,
            'company_base_price' => 'decimal:2',
            'company_billing_cycle' => 'integer',
            'company_billing_status' => BillingStatusEnum::class,
            'company_trial_ends_at' => 'datetime:Y-m-d H:i:s.u',
            'company_paid_ends_at' => 'datetime:Y-m-d H:i:s.u',
            'created_at' => 'datetime:Y-m-d H:i:s.u',
            'updated_at' => 'datetime:Y-m-d H:i:s.u',
            'deleted_at' => 'datetime:Y-m-d H:i:s.u',
        ];
    }

    //* 1. Beritahu Laravel apakah user sudah verifikasi
    public function hasVerifiedEmail() {
        return !is_null($this->company_email_verified_at);
    }

    //* 2. Beritahu Laravel cara menandai email sebagai terverifikasi
    public function markEmailAsVerified() {
        return $this->forceFill([
            'company_key_email' => Str::random(200),
            'company_email_verified_at' => Carbon::now()->format('Y-m-d H:i:s.u'),
            'company_is_status' => CompanyStatusEnum::ACTIVE
        ])->save();
    }

    //* 3. (Opsional) Jika kolom email Anda juga bukan bernama 'email'
    public function getEmailForVerification() {
        return $this->company_email;
    }

    public function getKeyEmail() {
        return $this?->company_key_email ?? '';
    }

    //* Relationships
    public function village() {
        return $this->belongsTo(RegVillage::class, 'company_village_id', 'village_id');
    }

    public function details() {
        return $this->hasOne(CompanyDetail::class, 'company_detail_company_id', 'company_id');
    }

    public function appAuthentication() {
        return $this->hasOne(CompanyAppAuthentication::class, 'company_app_authentication_company_id', 'company_id');
    }

    public function invoices() {
        return $this->hasMany(CompanyInvoice::class, 'company_invoice_company_id', 'company_id');
    }

    public function events() {
        return $this->hasMany(CompanyEvent::class, 'company_event_company_id', 'company_id');
    }

    //* Scopes
    public function scopeActiveVerified(Builder $query): Builder {
        return $query->whereNotNull('company_email_verified_at')
            ->where('company_is_status', CompanyStatusEnum::ACTIVE);
    }

    public function scopeBillingValid(Builder $query): Builder {
        return $query->where(function ($q) {
            $today = Carbon::today();

            $q->where(function ($sub) use ($today) {
                $sub->where('company_billing_status', BillingStatusEnum::PAID)->whereDate('company_paid_ends_at', '>=', $today);
            })->orWhere(function ($sub) use ($today) {
                $sub->where('company_billing_status', BillingStatusEnum::TRIAL)->whereDate('company_trial_ends_at', '>=', $today);
            });
        });
    }

    protected static function booted() {
        static::creating(function ($company) {
            $company->company_key_email = Str::random(200);
            $company->company_email_verified_at = null;
            $company->company_trial_ends_at = Carbon::now()->addDays(14);

            $guard = Auth::guard('api');
            $userCreator = $guard->user();
            if (!$userCreator || !$userCreator->auth_user_is_admin) {
                $company->company_is_status = CompanyStatusEnum::PENDING;
                $company->company_base_price = 0.00;
                $company->company_billing_cycle = 0;
                $company->company_billing_status = BillingStatusEnum::TRIAL;
            }
        });
    }
}

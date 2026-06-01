<?php

namespace App\Models;

use App\Traits\DatetimeFormatter;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyDetail extends Model {
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasUuids, DatetimeFormatter, HasFactory;

    protected $table = 'company_details';
    protected $keyType = 'string';
    protected $primaryKey = 'company_detail_company_id';
    protected $dateFormat = 'Y-m-d\TH:i:s.u\Z'; // ISO 8601
    public $incrementing = false;
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_detail_facebook',
        'company_detail_twitter',
        'company_detail_instagram',
        'company_detail_linkedin',
        'company_detail_smtp_host',
        'company_detail_smtp_port',
        'company_detail_smtp_name',
        'company_detail_smtp_user',
        'company_detail_smtp_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'created_at' => 'datetime:Y-m-d H:i:s.u',
            'updated_at' => 'datetime:Y-m-d H:i:s.u',
        ];
    }

    //* Relationships
    public function company() {
        return $this->belongsTo(Company::class, 'company_detail_company_id', 'company_id');
    }

    //* Accessors
    /*
    public function getSmtpConfigAttribute(): ?array {
        if (!$this->company_detail_smtp_host)
            return null;

        return [
            'host' => $this->company_detail_smtp_host,
            'port' => $this->company_detail_smtp_port,
            'encryption' => 'tls',
            'username' => $this->company_detail_smtp_user,
            'password' => $this->company_detail_smtp_password,
            'from' => [
                'address' => $this->company_detail_smtp_user,
                'name' => $this->company_detail_smtp_name ?? $this->company->company_name,
            ],
        ];
    }
    */

    //* Mutators
    /* protected function setCompanyDetailSmtpPasswordAttribute($value) {
        if ($value)
            $this->attributes['company_detail_smtp_password'] = encrypt($value);
    }

    protected function getCompanyDetailSmtpPasswordAttribute($value) {
        if ($value)
            return decrypt($value);

        return null;
    } */
}

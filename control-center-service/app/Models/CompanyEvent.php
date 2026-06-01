<?php

namespace App\Models;

use App\Enums\CompanyEventTypeEnum;
use App\Traits\DatetimeFormatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyEvent extends Model {
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasUuids, SoftDeletes, DatetimeFormatter, HasFactory;

    protected $table = 'company_events';
    protected $keyType = 'string';
    protected $primaryKey = 'company_event_id';
    protected $dateFormat = 'Y-m-d\TH:i:s.u\Z'; // ISO 8601
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_event_company_id',
        'company_event_type',
        'company_event_description',
        'company_event_metadata',
        'company_event_status',
        'created_by',
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
            'company_event_type' => CompanyEventTypeEnum::class,
            'company_event_metadata' => 'array',
            'company_event_status' => 'integer',
            'created_at' => 'datetime:Y-m-d H:i:s.u',
            'updated_at' => 'datetime:Y-m-d H:i:s.u',
            'deleted_at' => 'datetime:Y-m-d H:i:s.u',
        ];
    }

    //* Relationships
    public function company() {
        return $this->belongsTo(Company::class, 'company_event_company_id', 'company_id');
    }

    //* Scopes
    public function scopeLatestEvent(Builder $query, int $limit = 3): Builder {
        return $query->orderByDesc('created_at')
            ->limit($limit);
    }

    /**
     * created_by references auth_users.auth_user_id (external auth DB).
     * If you have an AuthUser model, set its $connection to the auth DB and reference it here.
     */
    /*
    public function creator() {
        return $this->belongsTo(\App\Models\AuthUser::class, 'created_by', 'auth_user_id');
    }
    */
}

<?php

namespace App\Models;

use App\Models\Concerns\{ApplyFilterPaginationScopes, ApplyFilterReadScopes, ApplyPerformDeleteAction, ApplyWithTrashedRelationScopes};
use App\Traits\DatetimeFormatter;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Country extends Model {
    use HasUuids, SoftDeletes, DatetimeFormatter, ApplyFilterPaginationScopes, ApplyFilterReadScopes, ApplyPerformDeleteAction, ApplyWithTrashedRelationScopes;

    protected $table = 'countries';
    protected $keyType = 'string';
    protected $primaryKey = 'country_id';
    protected $dateFormat = 'Y-m-d\TH:i:s.u\Z'; // ISO 8601
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'country_code',
        'country_alpha_3',
        'country_name',
        'country_capital',
        'country_phone',
        'country_continent_code',
        'country_currency_code'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'country_phone' => 'integer',
            'created_at' => 'datetime:Y-m-d H:i:s.u',
            'updated_at' => 'datetime:Y-m-d H:i:s.u',
            'deleted_at' => 'datetime:Y-m-d H:i:s.u',
        ];
    }

    /**
     ** Relationships
     */
    public function continent(): BelongsTo {
        return $this->belongsTo(Continent::class, 'country_continent_code', 'continent_code');
    }

    public function currency(): BelongsTo {
        return $this->belongsTo(Currency::class, 'country_currency_code', 'currency_code');
    }
}

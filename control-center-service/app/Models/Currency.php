<?php

namespace App\Models;

use App\Models\Concerns\{ApplyFilterPaginationScopes, ApplyFilterReadScopes, ApplyPerformDeleteAction, ApplyWithTrashedRelationScopes};
use App\Traits\DatetimeFormatter;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\{Builder, Model, SoftDeletes};
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model {
    use HasUuids, SoftDeletes, DatetimeFormatter, ApplyFilterPaginationScopes, ApplyFilterReadScopes, ApplyPerformDeleteAction, ApplyWithTrashedRelationScopes;

    protected $table = 'currencies';
    protected $keyType = 'string';
    protected $primaryKey = 'currency_id';
    protected $dateFormat = 'Y-m-d\TH:i:s.u\Z'; // ISO 8601
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'currency_code',
        'currency_name',
        'currency_symbol',
        'currency_is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'currency_is_active' => 'boolean',
            'created_at' => 'datetime:Y-m-d H:i:s.u',
            'updated_at' => 'datetime:Y-m-d H:i:s.u',
            'deleted_at' => 'datetime:Y-m-d H:i:s.u',
        ];
    }

    /**
     ** Relationships
     */
    public function countries(): HasMany {
        // Argumen: Nama model target, Foreign Key di tabel target, Local Key di tabel ini
        return $this->hasMany(Country::class, 'country_currency_code', 'currency_code');
    }

    /**
     ** Scopes
     */
    #[Scope]
    public function active(Builder $query): Builder {
        return $query->where('currency_is_active', true);
    }
}

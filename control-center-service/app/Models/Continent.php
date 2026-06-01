<?php

namespace App\Models;

use App\Models\Concerns\{ApplyFilterPaginationScopes, ApplyFilterReadScopes, ApplyPerformDeleteAction, ApplyWithTrashedRelationScopes};
use App\Traits\DatetimeFormatter;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Continent extends Model {
    use HasUuids, SoftDeletes, DatetimeFormatter, ApplyFilterPaginationScopes, ApplyFilterReadScopes, ApplyPerformDeleteAction, ApplyWithTrashedRelationScopes;

    protected $table = 'continents';
    protected $keyType = 'string';
    protected $primaryKey = 'continent_id';
    protected $dateFormat = 'Y-m-d\TH:i:s.u\Z'; // ISO 8601
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'continent_code',
        'continent_name'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'created_at' => 'datetime:Y-m-d H:i:s.u',
            'updated_at' => 'datetime:Y-m-d H:i:s.u',
            'deleted_at' => 'datetime:Y-m-d H:i:s.u',
        ];
    }

    /**
     ** Relationships
     */
    public function countries(): HasMany {
        return $this->hasMany(Country::class, 'country_continent_code', 'continent_code');
    }
}

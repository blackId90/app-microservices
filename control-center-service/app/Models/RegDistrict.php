<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegDistrict extends Model {
    use SoftDeletes;

    protected $table = 'reg_districts';
    protected $primaryKey = 'district_id';
    public $incrementing = true;
    protected $dateFormat = 'Y-m-d\TH:i:s.u\Z'; // ISO 8601

    protected $fillable = [
        'district_regency_id',
        'district_name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    //* Relationships
    public function regency() {
        return $this->belongsTo(RegRegency::class, 'district_regency_id', 'regency_id');
    }

    public function villages() {
        return $this->hasMany(RegVillage::class, 'village_district_id', 'district_id');
    }
}

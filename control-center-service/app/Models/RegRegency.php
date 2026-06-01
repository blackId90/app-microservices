<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegRegency extends Model {
    use SoftDeletes;

    protected $table = 'reg_regencies';
    protected $primaryKey = 'regency_id';
    public $incrementing = true;
    protected $dateFormat = 'Y-m-d\TH:i:s.u\Z'; // ISO 8601

    protected $fillable = [
        'regency_province_id',
        'regency_name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    //* Relationships
    public function province() {
        return $this->belongsTo(RegProvince::class, 'regency_province_id', 'province_id');
    }

    public function districts() {
        return $this->hasMany(RegDistrict::class, 'district_regency_id', 'regency_id');
    }
}

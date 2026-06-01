<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegProvince extends Model {
    use SoftDeletes;

    protected $table = 'reg_provinces';
    protected $primaryKey = 'province_id';
    public $incrementing = true;
    protected $dateFormat = 'Y-m-d\TH:i:s.u\Z'; // ISO 8601

    protected $fillable = [
        'province_name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    //* Relationships
    public function regencies() {
        return $this->hasMany(RegRegency::class, 'regency_province_id', 'province_id');
    }
}

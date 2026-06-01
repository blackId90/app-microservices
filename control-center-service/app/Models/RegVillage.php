<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegVillage extends Model {
    use SoftDeletes;

    protected $table = 'reg_villages';
    protected $primaryKey = 'village_id';
    public $incrementing = true;
    protected $dateFormat = 'Y-m-d\TH:i:s.u\Z'; // ISO 8601

    protected $fillable = [
        'village_district_id',
        'village_name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    //* Relationships
    public function district() {
        return $this->belongsTo(RegDistrict::class, 'village_district_id', 'district_id');
    }

    public function users() {
        return $this->hasMany(User::class, 'user_village_id', 'village_id');
    }

    public function companies() {
        return $this->hasMany(Company::class, 'company_village_id', 'village_id');
    }
}

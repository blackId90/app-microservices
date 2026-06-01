<?php

namespace App\Models;

use App\Models\Concerns\{ApplyFilterPaginationScopes, ApplyFilterReadScopes, ApplyPerformDeleteAction, ApplyWithTrashedRelationScopes};
use App\Traits\DatetimeFormatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuthRole extends Model {
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasUuids, SoftDeletes, DatetimeFormatter, HasFactory, ApplyFilterPaginationScopes, ApplyFilterReadScopes, ApplyPerformDeleteAction, ApplyWithTrashedRelationScopes;

    protected $table = 'auth_roles';
    protected $keyType = 'string';
    protected $primaryKey = 'auth_role_id';
    protected $dateFormat = 'Y-m-d\TH:i:s.u\Z'; // ISO 8601
    public $incrementing = false;

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
        // 'auth_role_id',
        'auth_role_slug',
        'auth_role_name',
        'auth_role_is_active',
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
            'auth_role_is_active' => 'boolean',
            'created_at' => 'datetime:Y-m-d H:i:s.u',
            'updated_at' => 'datetime:Y-m-d H:i:s.u',
            'deleted_at' => 'datetime:Y-m-d H:i:s.u',
        ];
    }

    //* Relationships
    public function users() {
        return $this->hasMany(AuthUser::class, 'auth_user_role_id', 'auth_role_id');
    }

    public function rolePermissions() {
        return $this->hasMany(AuthRolePermission::class, 'auth_role_permission_role_id', 'auth_role_id');
    }

    public function permissions() {
        return $this->belongsToMany(AuthPermission::class, 'auth_role_permissions', 'auth_role_permission_role_id', 'auth_role_permission_permission_id')
            ->withPivot('auth_role_permission_parameter');
    }

    //* Scopes
    public function scopeActive(Builder $query): Builder {
        return $query->where('auth_role_is_active', true);
    }
}

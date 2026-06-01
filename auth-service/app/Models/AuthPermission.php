<?php

namespace App\Models;

use App\Enums\{PermissionTargetEnum, PermissionTypeEnum};
use App\Models\Concerns\{ApplyFilterPaginationScopes, ApplyFilterReadScopes, ApplyPerformDeleteAction, ApplyWithTrashedRelationScopes};
use App\Traits\DatetimeFormatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuthPermission extends Model {
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasUuids, SoftDeletes, DatetimeFormatter, HasFactory, ApplyFilterPaginationScopes, ApplyFilterReadScopes, ApplyPerformDeleteAction, ApplyWithTrashedRelationScopes;

    protected $table = 'auth_permissions';
    protected $keyType = 'string';
    protected $primaryKey = 'auth_permission_id';
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
        // 'auth_permission_id',
        'auth_permission_type',
        'auth_permission_parent_permission_id',
        'auth_permission_slug',
        'auth_permission_title',
        'auth_permission_icon',
        'auth_permission_color',
        'auth_permission_url',
        'auth_permission_route',
        'auth_permission_target',
        'auth_permission_order',
        'auth_permission_is_active',
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
            'auth_permission_type' => PermissionTypeEnum::class,
            'auth_permission_target' => PermissionTargetEnum::class,
            'auth_permission_order' => 'integer',
            'auth_permission_is_active' => 'boolean',
            'created_at' => 'datetime:Y-m-d H:i:s.u',
            'updated_at' => 'datetime:Y-m-d H:i:s.u',
            'deleted_at' => 'datetime:Y-m-d H:i:s.u',
        ];
    }

    //* Relationships
    /**
     * Relasi ke parent permission (self‑relation).
     */
    public function parent() {
        return $this->belongsTo(AuthPermission::class, 'auth_permission_parent_permission_id', 'auth_permission_id')
            ->withTrashed()
            ->select([
                'auth_permission_id',
                'auth_permission_type',
                'auth_permission_slug',
                'auth_permission_title',
                'auth_permission_route',
                'created_at',
                'updated_at',
                'deleted_at' // Sangat disarankan jika menggunakan withTrashed agar tahu statusnya
            ]);
    }

    /**
     * Relasi ke child permissions (self‑relation).
     */
    public function children() {
        return $this->hasMany(AuthPermission::class, 'auth_permission_parent_permission_id', 'auth_permission_id');
    }

    /**
     * Relasi ke AuthRolePermission (pivot).
     */
    public function rolePermissions() {
        return $this->hasMany(AuthRolePermission::class, 'auth_role_permission_permission_id', 'auth_permission_id');
    }

    /**
     * Relasi many‑to‑many ke AuthRole.
     */
    public function roles() {
        return $this->belongsToMany(AuthRole::class, 'auth_role_permissions', 'auth_role_permission_permission_id', 'auth_role_permission_role_id')
            ->withPivot('auth_role_permission_parameter');
    }

    //* Scopes
    public function scopeActive(Builder $query): Builder {
        return $query->where('auth_permission_is_active', true);
    }
}

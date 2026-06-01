<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthRolePermission extends Model {
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasUuids, HasFactory;

    protected $table = 'auth_role_permissions';
    protected $keyType = 'string';
    protected $primaryKey = null;
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
        'auth_role_permission_role_id',
        'auth_role_permission_permission_id',
        'auth_role_permission_parameter',
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
            // 'auth_role_is_active' => 'boolean',
            // 'created_at' => 'datetime:Y-m-d H:i:s.u',
            // 'updated_at' => 'datetime:Y-m-d H:i:s.u',
            // 'deleted_at' => 'datetime:Y-m-d H:i:s.u',
        ];
    }

    //* Relationships
    public function role() {
        return $this->belongsTo(AuthRole::class, 'auth_role_permission_role_id', 'auth_role_id');
    }

    /**
     * Relasi ke AuthPermission.
     */
    public function permission() {
        return $this->belongsTo(AuthPermission::class, 'auth_role_permission_permission_id', 'auth_permission_id');
    }
}

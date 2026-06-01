<?php

namespace App\Models;

use App\Enums\UserStatusEnum;
use App\Traits\DatetimeFormatter;
// use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SyncAuthUser extends Model {
    use SoftDeletes, DatetimeFormatter;

    protected $table = 'sync_auth_users';
    protected $keyType = 'string';
    protected $primaryKey = 'auth_user_id';
    protected $dateFormat = 'Y-m-d\TH:i:s.u\Z'; // ISO 8601
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'auth_user_id',
        'auth_user_email',
        'auth_user_username',
        'auth_user_company_id',
        'auth_user_is_admin',
        'auth_user_is_status',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'auth_user_is_admin' => 'boolean',
            'auth_user_is_status' => UserStatusEnum::class,
            'created_at' => 'datetime:Y-m-d H:i:s.u',
            'updated_at' => 'datetime:Y-m-d H:i:s.u',
            'deleted_at' => 'datetime:Y-m-d H:i:s.u',
        ];
    }

    /**
     ** Relationships
     */
    public function profile(): HasOne {
        return $this->hasOne(User::class, 'user_auth_user_id', 'auth_user_id');
    }

    /**
     ** Accessors & Mutators
     */
    /*
    protected function authUserIsAdmin(): Attribute {
        return Attribute::make(
            set: fn($value) => filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false',
        );
    }
    */
}

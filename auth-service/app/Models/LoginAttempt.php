<?php

namespace App\Models;

use App\Enums\LoginAttemptTypeEnum;
use App\Traits\DatetimeFormatter;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Illuminate\Support\Str;

class LoginAttempt extends Model {
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasUuids, SoftDeletes, DatetimeFormatter, HasFactory;

    protected $table = 'login_attempts';
    protected $keyType = 'string';
    protected $primaryKey = 'login_attempt_id';
    protected $dateFormat = 'Y-m-d\TH:i:s.u\Z'; // ISO 8601
    // protected $datetimeFormat = 'Y-m-d\TH:i:s.u\Z'; // 'Y-m-d H:i:s.u';
    public $incrementing = false;

    protected $guarded = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        // 'login_attempt_type',
        // 'login_attempt_ip_address',
        // 'login_attempt_user_agent',
        // 'login_attempt_is_status',
        // 'created_by'
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
            'login_attempt_type' => LoginAttemptTypeEnum::class,
            'login_attempt_ip_address' => 'string',
            'login_attempt_user_agent' => 'string',
            'login_attempt_is_status' => 'boolean',
            'created_by' => 'string',
            'created_at' => 'datetime:Y-m-d H:i:s.u',
            // 'created_at' => 'datetime',
            // 'updated_at' => 'datetime',
            // 'deleted_at' => 'datetime',
        ];
    }

    /*
    protected static function booted(): void {
        static::creating(function (self $model) {
            $model->login_attempt_id = $model->login_attempt_id ?? Str::uuid()->toString();
        });
    }
    */

    public function user() {
        return $this->belongsTo(AuthUser::class, 'created_by', 'auth_user_id');
    }
}

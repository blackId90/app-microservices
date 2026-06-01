<?php

namespace App\Models;

use App\Enums\UserStatusEnum;
use App\Models\Concerns\{ApplyFilterReadScopes, ApplyPerformDeleteAction, ApplyWithTrashedRelationScopes};
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\DatetimeFormatter;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\{Carbon, Str};
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Contracts\JWTSubject;

class AuthUser extends Authenticatable implements JWTSubject, MustVerifyEmail {
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasUuids, SoftDeletes, DatetimeFormatter, HasFactory, Notifiable, ApplyFilterReadScopes, ApplyPerformDeleteAction, ApplyWithTrashedRelationScopes;

    protected $table = 'auth_users';
    protected $keyType = 'string';
    protected $primaryKey = 'auth_user_id';
    protected $authIdentifierName = 'auth_user_username';
    protected $authPasswordName = 'auth_user_password';
    public $incrementing = false;
    protected $dateFormat = 'Y-m-d\TH:i:s.u\Z'; // ISO 8601
    protected $datetimeFormat = 'Y-m-d\TH:i:s.u\Z'; // 'Y-m-d H:i:s.u';


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'auth_user_username',
        'auth_user_email',
        'auth_user_password',
        'auth_user_company_id',
        // 'auth_user_role_id',
        // 'auth_user_is_admin',
        // 'auth_user_is_status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        // 'auth_user_is_admin',
        'auth_user_key_email',
        'auth_user_email_verified_at',
        'auth_user_password',
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
            'auth_user_email_verified_at' => 'datetime:Y-m-d H:i:s.u',
            'auth_user_password' => 'hashed',
            // 'created_at' => 'datetime:Y-m-d H:i:s.u',
        ];
    }

    /*
    public function getAuthIdentifierName() {
        return 'auth_user_id'; // Primary key
    }

    public function getAuthPassword() {
        return $this->auth_user_password;
    }
    */

    public function getAuthIdentifierName() {
        return $this->primaryKey;
    }

    public function getAuthPassword() {
        return $this->{$this->authPasswordName};
    }

    /**
     * Get the identifier that will be stored in the JWT token.
     */
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    /**
     * Return an array with custom claims to be added to the JWT token.
     */
    public function getJWTCustomClaims() {
        return [
            'iss' => config('app.url'),
            'jti' => 'jwt_' . (string) Str::uuid7(now()), // uniqid('jwt_', true),
            'is_admin' => $this->auth_user_is_admin,
            'company' => $this->auth_user_company_id,
            'role' => $this->auth_user_role_id
        ];
    }

    //* 1. Beritahu Laravel apakah user sudah verifikasi
    public function hasVerifiedEmail() {
        return !is_null($this->auth_user_email_verified_at);
    }

    //* 2. Beritahu Laravel cara menandai email sebagai terverifikasi
    public function markEmailAsVerified() {
        return $this->forceFill([
            'auth_user_key_email' => Str::random(200),
            'auth_user_email_verified_at' => Carbon::now()->format('Y-m-d H:i:s.u'), // $this->freshTimestamp(),
            'auth_user_is_status' => UserStatusEnum::ACTIVE
        ])->save();
    }

    //* 3. (Opsional) Jika kolom email Anda juga bukan bernama 'email'
    public function getEmailForVerification() {
        return $this->auth_user_email;
    }

    public function getKeyEmail() {
        return $this?->auth_user_key_email ?? '';
    }

    /**
     * Format payloads data send to Redis
     */
    public function toSyncPayload(string $action): array {
        return [
            'action'     => $action,
            'id'         => $this->auth_user_id,
            'email'      => $this->auth_user_email,
            'username'   => $this->auth_user_username,
            'company'    => $this->auth_user_company_id,
            'is_admin'   => $this->auth_user_is_admin,
            'status'     => $this->auth_user_is_status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }

    /**
     * Dispatch Job to Redis
     */
    public function sync(string $action = 'upsert'): void {
        $payload = $this->toSyncPayload($action);

        //* Store to table outbox
        Outbox::create([
            'topic' => 'auth_user_sync',
            'payload' => $payload
        ]);

        //* Dispatch Job for Auth Service (update field processed_at)
        \App\Jobs\SyncAuthUserJob::dispatch($payload)
            ->onQueue('auth_internal_sync')
            ->afterCommit();

        //* Dispatch Job for Control Center Service (afterCommit() menjamin Job dikirim ke Redis HANYA JIKA data auth user dan data outbox sudah sukses tersimpan di DB).
        \App\Jobs\SyncAuthUserJob::dispatch($payload)
            ->onQueue('sync_auth_queue')
            ->afterCommit();
    }

    //* Relationships
    public function role() {
        return $this->belongsTo(AuthRole::class, 'auth_user_role_id', 'auth_role_id');
    }

    public function loginAttempts() {
        return $this->hasMany(LoginAttempt::class, 'created_by', 'auth_user_id');
    }

    /*
    public function latestLoginAttempt() {
        return $this->hasOne(LoginAttempt::class, 'created_by', 'auth_user_id')->latest('created_at');
    }
    */

    protected static function booted() {
        static::creating(function ($user) {
            $user->auth_user_key_email = Str::random(200);

            $creator = Auth::user();
            if (!$creator || !$creator->auth_user_is_admin) {
                $user->auth_user_is_admin = false;
                $user->auth_user_is_status = UserStatusEnum::PENDING;
            }
        });
    }
}

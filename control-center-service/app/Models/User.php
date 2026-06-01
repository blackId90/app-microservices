<?php

namespace App\Models;

use App\Enums\UserGenderEnum;
use App\Models\Concerns\{ApplyFilterPaginationScopes, ApplyFilterReadScopes, ApplyPerformDeleteAction, ApplyWithTrashedRelationScopes};
use App\Traits\DatetimeFormatter;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
// use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasUuids, SoftDeletes, DatetimeFormatter, HasFactory, Notifiable, ApplyFilterPaginationScopes, ApplyFilterReadScopes, ApplyPerformDeleteAction, ApplyWithTrashedRelationScopes;

    protected $table = 'users';
    protected $keyType = 'string';
    protected $primaryKey = 'user_id';
    protected $dateFormat = 'Y-m-d\TH:i:s.u\Z'; // ISO 8601
    // protected $datetimeFormat = 'Y-m-d\TH:i:s.u\Z'; // 'Y-m-d H:i:s.u';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_auth_user_id',
        'user_avatar',
        'user_first_name',
        'user_last_name',
        'user_gender',
        'user_address',
        'user_village_id',
        'user_zip_code',
        'user_phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ['user_village_id'];

    /**
     * The relations to eager load on every query.
     * Nested eager loading for location region
     *
     * @var array
     */
    protected $with = ['village.district.regency.province'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    // protected $appends = ['user_full_name'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'user_gender' => UserGenderEnum::class,
            'created_at' => 'datetime:Y-m-d H:i:s.u',
            'updated_at' => 'datetime:Y-m-d H:i:s.u',
            'deleted_at' => 'datetime:Y-m-d H:i:s.u',
        ];
    }

    /**
     ** Relationships
     */
    /**
     * Relations to auth_users
     * Communication dengan Auth Service
     */
    public function authUser() {
        return $this->belongsTo(SyncAuthUser::class, 'user_auth_user_id', 'auth_user_id');
    }

    /**
     * Relasi ke village (reg_villages)
     */
    public function village() {
        return $this->belongsTo(RegVillage::class, 'user_village_id', 'village_id');
    }

    /**
     ** Mutators & Accessors
     */
    /*
    protected function userFullName(): Attribute {
        return Attribute::make(
            get: fn() => trim("{$this->user_first_name} {$this->user_last_name}"),
            // set: fn (string $value) => ['first_name' => explode(' ', $value)[0]], // jika ada setter
        );
    }
    */

    /**
     ** Functions
     */
    /**
     * Computed properties get avatar.
     *
     * @return null|string
     */
    /*
    public function getAvatarUrlAttribute(): ?string {
        if ($this->user_avatar)
            return Storage::disk('avatars')->url($this->user_avatar);

        //* Generate initials avatar
        $initials = strtoupper(substr($this->user_first_name, 0, 1) . substr($this->user_last_name, 0, 1));

        return "https://ui-avatars.com/api/?name={$initials}&background=random";
    }
    */

    /**
     * PHP 8.4 Property Hook untuk auto-format phone.
     *
     * @var string $user_phone
     */
    private string $user_phone {
        set {
            //* Remove non-numeric characters
            $cleaned = preg_replace('/[^0-9]/', '', $value);

            //* Pastikan format regex sesuai dengan panjang nomor telepon Indonesia. Format: +62 812-3456-7890
            // $formatted = preg_replace('/(\d{3})(\d{4})(\d{4})/', '$1-$2-$3', $cleaned);
            $formatted = preg_replace('/(\d{3})(\d{4})(\d+)/', '$1-$2-$3', $cleaned);
            $this->attributes['user_phone'] = '+62 ' . $formatted;
        }
    }
}

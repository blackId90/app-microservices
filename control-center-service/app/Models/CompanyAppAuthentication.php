<?php

namespace App\Models;

use App\Traits\DatetimeFormatter;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyAppAuthentication extends Model {
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasUuids, DatetimeFormatter, HasFactory;

    protected $table = 'company_app_authentication';
    protected $keyType = 'string';
    protected $primaryKey = 'company_app_authentication_company_id';
    protected $dateFormat = 'Y-m-d\TH:i:s.u\Z'; // ISO 8601
    public $incrementing = false;
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        //* Domain & DB
        'company_app_authentication_domain',
        'company_app_authentication_db_host',
        'company_app_authentication_db_port',
        'company_app_authentication_db_database',
        'company_app_authentication_db_schema',
        'company_app_authentication_db_username',
        'company_app_authentication_db_password',
        'company_app_authentication_db_prefix',
        //* Redis
        'company_app_authentication_redis_host',
        'company_app_authentication_redis_port',
        'company_app_authentication_redis_database',
        'company_app_authentication_redis_schema',
        'company_app_authentication_redis_username',
        'company_app_authentication_redis_password',
        'company_app_authentication_redis_prefix',
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
            'created_at' => 'datetime:Y-m-d H:i:s.u',
            'updated_at' => 'datetime:Y-m-d H:i:s.u',
        ];
    }

    //* Relationships
    public function company() {
        return $this->belongsTo(Company::class, 'company_app_authentication_company_id', 'company_id');
    }

    //* Accessors
    /*
    public function getDatabaseConfigAttribute(): array {
        return [
            'driver' => 'pgsql',
            'host' => $this->company_app_authentication_db_host,
            'port' => $this->company_app_authentication_db_port,
            'database' => $this->company_app_authentication_db_database,
            'schema' => $this->company_app_authentication_db_schema,
            'username' => $this->company_app_authentication_db_username,
            'password' => $this->company_app_authentication_db_password,
            'charset' => 'utf8',
            'prefix' => $this->company_app_authentication_db_prefix,
            'prefix_indexes' => true,
            'search_path' => $this->company_app_authentication_db_schema,
            'sslmode' => 'prefer',
        ];
    }

    public function getRedisConfigAttribute(): array {
        return [
            'host' => $this->company_app_authentication_redis_host,
            'port' => $this->company_app_authentication_redis_port,
            'database' => $this->company_app_authentication_redis_database,
            'username' => $this->company_app_authentication_redis_username,
            'password' => $this->company_app_authentication_redis_password,
            'prefix' => $this->company_app_authentication_redis_prefix,
        ];
    } */

    //* Mutators
    /*
    protected function setCompanyAppAuthenticationDbPasswordAttribute($value) {
        $this->attributes['company_app_authentication_db_password'] = encrypt($value);
    }

    protected function getCompanyAppAuthenticationDbPasswordAttribute($value) {
        return decrypt($value);
    }

    protected function setCompanyAppAuthenticationRedisPasswordAttribute($value) {
        $this->attributes['company_app_authentication_redis_password'] = encrypt($value);
    }

    protected function getCompanyAppAuthenticationRedisPasswordAttribute($value) {
        return decrypt($value);
    }
    */
}

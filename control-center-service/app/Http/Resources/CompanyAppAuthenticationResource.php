<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyAppAuthenticationResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        // return parent::toArray($request);
        return [
            'company_app_authentication_company_id' => $this->company_app_authentication_company_id,
            'company_app_authentication_domain' => $this->company_app_authentication_domain,
            'company_app_authentication_db_host' => $this->company_app_authentication_db_host,
            'company_app_authentication_db_port' => $this->company_app_authentication_db_port,
            'company_app_authentication_db_database' => $this->company_app_authentication_db_database,
            'company_app_authentication_db_schema' => $this->company_app_authentication_db_schema,
            'company_app_authentication_db_username' => $this->company_app_authentication_db_username,
            'company_app_authentication_db_password' => $this->company_app_authentication_db_password,
            'company_app_authentication_db_prefix' => $this->company_app_authentication_db_prefix,
            'company_app_authentication_redis_host' => $this->company_app_authentication_redis_host,
            'company_app_authentication_redis_port' => $this->company_app_authentication_redis_port,
            'company_app_authentication_redis_database' => $this->company_app_authentication_redis_database,
            'company_app_authentication_redis_schema' => $this->company_app_authentication_redis_schema,
            'company_app_authentication_redis_username' => $this->company_app_authentication_redis_username,
            'company_app_authentication_redis_password' => $this->company_app_authentication_redis_password,
            'company_app_authentication_redis_prefix' => $this->company_app_authentication_redis_prefix,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}

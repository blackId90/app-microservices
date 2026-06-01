<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        // return parent::toArray($request);

        //* Cek apakah ini hasil manual join berdasarkan prefix unik
        if (isset($this->sync_auth_users_auth_user_id))
            return $this->mapJoinData();

        return $this->mapEloquentData();
    }

    /**
     * Mapping data khusus hasil Manual Join (Query Builder)
     */
    private function mapJoinData(): array {
        return [
            'auth_user_id' => $this?->sync_auth_users_auth_user_id,
            'auth_user_email' => $this?->sync_auth_users_auth_user_email ?? null,
            'auth_user_username' => $this?->sync_auth_users_auth_user_username ?? null,
            // 'auth_user_company_id' => $this?->sync_auth_users_auth_user_company_id ?? null,
            'auth_user_is_admin' => $this?->sync_auth_users_auth_user_is_admin ?? null,
            'auth_user_is_status' => $this?->sync_auth_users_auth_user_is_status ?? null,
            'created_at' => $this?->sync_auth_users_created_at ?? null,
            'updated_at' => $this?->sync_auth_users_updated_at ?? null,
            'deleted_at' => $this?->sync_auth_users_deleted_at ?? null
        ];
    }

    /**
     * Mapping data standar Eloquent Model
     */
    private function mapEloquentData(): array {
        return [
            'auth_user_id' => $this->auth_user_id,
            'auth_user_email' => $this->auth_user_email ?? null,
            'auth_user_username' => $this->auth_user_username ?? null,
            // 'auth_user_company_id' => $this->auth_user_company_id ?? null,
            'auth_user_is_admin' => $this->auth_user_is_admin ?? null,
            'auth_user_is_status' => $this->auth_user_is_status ?? null,
            'created_at' => $this->created_at ?? null,
            'updated_at' => $this->updated_at ?? null,
            'deleted_at' => $this->deleted_at ?? null
        ];
    }
}

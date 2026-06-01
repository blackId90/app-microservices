<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        // return parent::toArray($request);
        return [
            'auth_user_id' => $this->auth_user_id,
            'auth_user_email' => $this->auth_user_email,
            'auth_user_username' => $this->auth_user_username,
            'auth_user_company_id' => $this->auth_user_company_id,
            'auth_user_role_id' => $this->auth_user_role_id,
            'auth_user_is_admin' => $this->auth_user_is_admin,
            'auth_user_is_status' => $this->auth_user_is_status,
            /*
            'last_login' => LoginAttemptResource::collection($this->whenLoaded('loginAttempts')),
            'last_login' => new LoginAttemptResource($this->latestLoginAttempt),

            'last_login' => $this->latestLoginAttempt instanceof \App\Models\LoginAttempt ? new LoginAttemptResource($this->latestLoginAttempt) : null,
            'last_login' => $this->latestLoginAttempt ? new LoginAttemptResource($this->latestLoginAttempt) : null,
            */

            'profile' => $this->whenLoaded('profile_user'),
            'role' => $this->whenLoaded('role', fn() => new AuthRoleResource($this->role)),
            'company' => $this->whenLoaded('company'),

            //* Cek metadata dari method additional() dari setRelation di Controller + last_login jika relasi di-load
            'meta' => $this->when(!empty($this->additional) || $this->relationLoaded('latestLoginAttempt'), function () {
                return array_merge($this->additional, [
                    'last_login' => $this->whenLoaded('latestLoginAttempt', fn() => new LoginAttemptResource($this->latestLoginAttempt))
                ]);
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}

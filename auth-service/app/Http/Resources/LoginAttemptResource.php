<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginAttemptResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        // return parent::toArray($request);
        return [
            'login_attempt_id' => $this->login_attempt_id,
            'login_attempt_type' => $this->login_attempt_type,
            'login_attempt_identifier' => $this->login_attempt_identifier,
            'login_attempt_ip_address' => $this->login_attempt_ip_address,
            'login_attempt_user_agent' => $this->login_attempt_user_agent,
            'login_attempt_is_status' => $this->login_attempt_is_status,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}

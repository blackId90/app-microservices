<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPermissionResource extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        // return parent::toArray($request);

        return [
            'permission_id' => $this->permission_id,
            'permission_type' => $this->permission_type,
            'permission_parent' => $this->permission_parent,
            'permission_slug' => $this->permission_slug,
            'permission_title' => $this->permission_title,
            'permission_icon' => $this->permission_icon,
            'permission_color' => $this->permission_color,
            'permission_url' => $this->permission_url,
            'permission_route' => $this->permission_route,
            'permission_target' => $this->permission_target,
            'permission_order' => $this->permission_order,
            'permission_parameter' => $this->permission_parameter,
            'permission_is_active' => $this->permission_is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

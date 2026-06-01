<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthPermissionResource extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        // return parent::toArray($request);
        return [
            'auth_permission_id' => $this->auth_permission_id,
            'auth_permission_type' => $this->auth_permission_type,
            'auth_permission_parent' => self::setPermissionParents($this->resource), // self::setPermissionParents($this),
            'auth_permission_slug' => $this->auth_permission_slug,
            'auth_permission_title' => $this->auth_permission_title,
            'auth_permission_icon' => $this->auth_permission_icon,
            'auth_permission_color' => $this->auth_permission_color,
            'auth_permission_url' => $this->auth_permission_url,
            'auth_permission_route' => $this->auth_permission_route,
            'auth_permission_target' => $this->auth_permission_target,
            //* Ambil parameter yang disuntikkan di atas
            'auth_permission_parameter' => $this->auth_role_permission_parameter ?? null,
            'auth_permission_order' => $this->auth_permission_order,
            'auth_permission_is_active' => $this->auth_permission_is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }

    /**
     * Combining manual JOIN and Eager Loading results
     * Become a standard object for the Resource API.
     */
    private static function setPermissionParents($model) {
        return match (true) {
            //* If data from manual join
            isset($model->auth_permission_parent_id) => [
                'auth_permission_id' => $model?->auth_permission_parent_id,
                'auth_permission_type' => $model?->auth_permission_parent_type ?? null,
                'auth_permission_slug' => $model?->auth_permission_parent_slug ?? null,
                'auth_permission_title' => $model?->auth_permission_parent_title ?? null,
                'auth_permission_route' => $model?->auth_permission_parent_route ?? null,
                'created_at' => $model?->auth_permission_parent_created_at ?? null,
                'updated_at' => $model?->auth_permission_parent_updated_at ?? null,
                'deleted_at' => $model?->auth_permission_parent_deleted_at ?? null
            ],
            //* If data from Eager Loading with('parent')
            $model->relationLoaded('parent') && $model->parent => [
                'auth_permission_id' => $model->parent?->auth_permission_id,
                'auth_permission_type' => $model->parent?->auth_permission_type ?? null,
                'auth_permission_slug' => $model->parent?->auth_permission_slug ?? null,
                'auth_permission_title' => $model->parent?->auth_permission_title ?? null,
                'auth_permission_route' => $model->parent?->auth_permission_route ?? null,
                'created_at' => $model->parent?->created_at ?? null,
                'updated_at' => $model->parent?->updated_at ?? null,
                'deleted_at' => $model->parent?->deleted_at ?? null
            ],
            default => null,
        };
    }
}

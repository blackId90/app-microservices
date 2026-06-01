<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthRoleResource extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        // return parent::toArray($request);
        return [
            'auth_role_id' => $this->auth_role_id,
            'auth_role_slug' => $this->auth_role_slug,
            'auth_role_name' => $this->auth_role_name,
            'auth_role_is_active' => $this->auth_role_is_active,
            /*
            'permissions' => $this->whenLoaded('permissions', function() {
                return $this->permissions->map(function($permission) {
                    return self::setPermissions($permission);
                });
            }),
            */
            //* Handle dua jenis pemanggilan relasi
            'permissions' => $this->when(
                $this->relationLoaded('permissions') || $this->relationLoaded('rolePermissions'),
                function () {
                    if ($this->relationLoaded('permissions'))
                        return AuthPermissionResource::collection($this->permissions);

                    return AuthPermissionResource::collection(
                        $this->rolePermissions->map(function ($rolePermission) {
                            $permission = $rolePermission->permission;

                            //* Suntikkan parameter dari tabel pivot ke atribut model permission, agar bisa diakses di AuthPermissionResource
                            if ($permission)
                                $permission->auth_role_permission_parameter = $rolePermission->auth_role_permission_parameter;

                            return $permission;
                        })->filter() //* Menghapus null jika ada permission yang tidak ditemukan
                    );
                }
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }

    /*
    private static function setPermissions($permission) {
        return [
            'auth_permission_id' => $permission->auth_permission_id,
            'auth_permission_type' => $permission->auth_permission_type,
            'auth_permission_parent' => self::setPermissionParents($permission),
            'auth_permission_slug' => $permission->auth_permission_slug,
            'auth_permission_title' => $permission->auth_permission_title,
            'auth_permission_icon' => $permission->auth_permission_icon,
            'auth_permission_color' => $permission->auth_permission_color,
            'auth_permission_url' => $permission->auth_permission_url,
            'auth_permission_route' => $permission->auth_permission_route,
            'auth_permission_target' => $permission->auth_permission_target,
            'auth_permission_order' => $permission->auth_permission_order,
            'auth_permission_is_active' => $permission->auth_permission_is_active,
            'created_at' => $permission->created_at,
            'updated_at' => $permission->updated_at,
            'deleted_at' => $permission->deleted_at,
        ];
    }
    */

    /**
     * Combining manual JOIN and Eager Loading results
     * Become a standard object for the Resource API.
     */
    /*
    private static function setPermissionParents($model) {
        return match (true) {
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
            default => null,
        };
    }
    */
}

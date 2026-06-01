<?php

namespace App\Repositories;

use App\Models\AuthRolePermission;
use App\Repositories\Interfaces\AuthRolePermissionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AuthRolePermissionRepository implements AuthRolePermissionRepositoryInterface {

    public function batchInsert(array $data): bool {
        return AuthRolePermission::insert($data);
    }

    public function existsByRolePermissions(string $authRoleId, array $authPermissionIds): bool {
        $query = AuthRolePermission::where('auth_role_permission_role_id', $authRoleId)
            ->whereIn('auth_role_permission_permission_id', $authPermissionIds);

        return $query->exists();
    }

    public function getAllRolePermissionsById(string $authRolePermissionRoleId): Collection {
        return AuthRolePermission::where('auth_role_permission_role_id', $authRolePermissionRoleId)
            ->get();
    }

    public function getAllRolePermissionsByIds(array $authRolePermissionRoleIds): Collection {
        return AuthRolePermission::whereIn('auth_role_permission_role_id', $authRolePermissionRoleIds)
            ->get();
    }

    public function findByRoleIdAndPermissionId(string $authRolePermissionRoleId, string $authRolePermissionPermissionId): ?AuthRolePermission {
        return AuthRolePermission::where('auth_role_permission_role_id', $authRolePermissionRoleId)
            ->where('auth_role_permission_permission_id', $authRolePermissionPermissionId)
            ->first();
    }

    public function updateOrInsertPermission(string $roleId, string $permissionId, ?int $parameter): bool {
        $result = AuthRolePermission::updateOrInsert(
            [
                'auth_role_permission_role_id' => $roleId,
                'auth_role_permission_permission_id' => $permissionId,
            ],
            ['auth_role_permission_parameter' => $parameter]
        );

        return $result;
    }

    public function deleteByRoleId(string $roleId): bool {
        $deleted = AuthRolePermission::where('auth_role_permission_role_id', $roleId)
            ->delete();

        return $deleted > 0;
    }

    public function bulkInsertPermissions(string $roleId, array $permissions): bool {
        if (empty($permissions))
            return true;

        $dataToInsert = [];
        foreach ($permissions as $permissionId => $parameter) {
            if ($parameter !== null) {
                $dataToInsert[] = [
                    'auth_role_permission_role_id' => $roleId,
                    'auth_role_permission_permission_id' => $permissionId,
                    'auth_role_permission_parameter' => $parameter,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (empty($dataToInsert))
            return true;

        return AuthRolePermission::insert($dataToInsert);
    }
}

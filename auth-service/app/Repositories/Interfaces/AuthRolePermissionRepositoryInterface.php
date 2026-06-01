<?php

namespace App\Repositories\Interfaces;

use App\Models\AuthRolePermission;
use Illuminate\Database\Eloquent\Collection;

interface AuthRolePermissionRepositoryInterface {

    public function batchInsert(array $data): bool;

    public function existsByRolePermissions(string $authRoleId, array $authPermissionIds): bool;

    public function getAllRolePermissionsById(string $authRolePermissionRoleId): Collection;

    public function getAllRolePermissionsByIds(array $authRolePermissionRoleIds): Collection;

    public function findByRoleIdAndPermissionId(string $authRolePermissionRoleId, string $authRolePermissionPermissionId): ?AuthRolePermission;

    public function updateOrInsertPermission(string $roleId, string $permissionId, ?int $parameter): bool;

    public function deleteByRoleId(string $roleId): bool;

    public function bulkInsertPermissions(string $roleId, array $permissions): bool;
}

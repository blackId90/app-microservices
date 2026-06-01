<?php

namespace App\Services\Applications;

use App\Repositories\Interfaces\AuthRolePermissionRepositoryInterface;

class AuthRolePermissionService {

    public function __construct(
        protected AuthRolePermissionRepositoryInterface $authRolePermissionRepository
    ) {}

    public function storeBatch(string $roleId, array $permissions) {
        //* Mapping data before send to repository
        $batchData = collect($permissions)->map(function ($item) use ($roleId) {
            return [
                'auth_role_permission_role_id' => $roleId,
                'auth_role_permission_permission_id' => $item['permission_id'],
                'auth_role_permission_parameter' => $item['role_permission_parameter'] ?? null,
            ];
        })->all();

        return $this->authRolePermissionRepository->batchInsert($batchData);
    }

    public function updateBatch(string $roleId, array $permissions) {
        //* Delete all old permissions for this role
        $this->authRolePermissionRepository->deleteByRoleId($roleId);

        //* Insert new data use storeBatch
        return $this->storeBatch($roleId, $permissions);
    }
}

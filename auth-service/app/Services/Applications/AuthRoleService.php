<?php

namespace App\Services\Applications;

use App\Enums\{AppAuthResponseCode, TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum, TypeUpdateEnum};
use App\Exceptions\{AppAuthException, ValidationFormRequestException};
use App\Models\AuthRole;
use App\Repositories\Interfaces\{AuthRolePermissionRepositoryInterface, AuthRoleRepositoryInterface};
use App\Services\BaseApplicationService;
use App\Traits\LogAudit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AuthRoleService extends BaseApplicationService {
    use LogAudit;

    public function __construct(
        protected AuthRoleRepositoryInterface $authRoleRepository,
        protected AuthRolePermissionRepositoryInterface $authRolePermissionRepository,
        protected AuthRolePermissionService $authRolePermissionService,
    ) {}

    public function listAuthRoles(?string $search, int $perPage, int $typeList = TypeBrowseEnum::WITHOUT_DELETED): LengthAwarePaginator {
        $this->validationQueryParamsTypeList($typeList);

        return $this->authRoleRepository->paginateWithSearchAndType($search, $perPage, $typeList);
    }

    public function createRole(array $payload): AuthRole {
        $this->existsBySlug($payload['auth_role_slug']);

        return DB::transaction(function () use ($payload) {
            //* 1. Insert Auth Role
            $role = $this->authRoleRepository->create($payload);

            //* Check unique Composite Key (Data integrity check dalam payload)
            // $permissionIds = collect($payload['auth_role_permissions'])->pluck('permission_id');

            //* 2. Delegate batch inserts to AuthRolePermissionService
            $this->authRolePermissionService->storeBatch(
                $role->auth_role_id,
                $payload['auth_role_permissions']
            );

            return $role;
        });
    }

    public function getRoleById(string $authRoleId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, array $relations = []): ?AuthRole {
        $this->validationQueryParamsTypeRead($typeRead);

        try {
            $dataRole = $this->authRoleRepository->findById($authRoleId, $typeRead, true, $relations);

            return $dataRole;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new AppAuthException(AppAuthResponseCode::NotFound);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function updateRole(string $authRoleId, array $payload, int $typeUpdate = TypeUpdateEnum::WITHOUT_DELETED): AuthRole {
        $this->validationQueryParamsTypeUpdate($typeUpdate);

        try {
            //* Find data role by auth_role_id
            $role = $this->authRoleRepository->findById($authRoleId, $typeUpdate, true);

            //* Check existing auth_role_slug
            $this->existsBySlug($payload['auth_role_slug'], $authRoleId);

            return DB::transaction(function () use ($role, $payload) {
                //* 1. Update Auth Role
                $dataRole = $this->authRoleRepository->update($role, $payload);

                //* 2. Update data table Auth Role Permissions. (If there are data permissions in the payload, delegate batch inserts to AuthRolePermissionService)
                if (isset($payload['auth_role_permissions'])) {
                    $this->authRolePermissionService->updateBatch(
                        $role->auth_role_id,
                        $payload['auth_role_permissions']
                    );
                }

                return $dataRole;
            });
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new AppAuthException(AppAuthResponseCode::NotFound);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function deleteRole(string $authRoleId, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): array {
        $this->validationQueryParamsTypeDelete($typeDelete);

        //* Determine readType based on typeDelete
        $typeRead = $this->DetermineTypeReadBaseOnTypeDelete($typeDelete);

        try {
            $dataRole = $this->authRoleRepository->findById($authRoleId, $typeRead);

            return DB::transaction(function () use ($dataRole, $typeDelete) {
                //* Delete table Auth Role Permissions if value typeDelete PERMANENT DELETE or HARD DELETE
                if (in_array($typeDelete, [TypeDeleteEnum::PERMANENT_DELETE, TypeDeleteEnum::HARD_DELETE]))
                    $this->authRolePermissionRepository->deleteByRoleId($dataRole->auth_role_id);

                //* Delete table Auth Role
                $this->authRoleRepository->delete($dataRole, $typeDelete);

                //* Mapping message from typeDelete
                $message = $this->MappingMessageTypeDelete($typeDelete);

                return compact('message');
            });
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new AppAuthException(AppAuthResponseCode::NotFound);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function optionRoles(?string $search, int $perPage = 10, int $typeList = TypeBrowseEnum::WITHOUT_DELETED, array $filterWhereIn = []): LengthAwarePaginator {
        $this->validationQueryParamsTypeList($typeList);

        return $this->authRoleRepository->paginateWithSearchAndType($search, $perPage, $typeList, $filterWhereIn);
    }

    public function getAuthRole(string $authRoleId) {
        //* Get Data Company Profile
        $roleData = $this->authRoleRepository->findByRoleId($authRoleId);

        return $roleData;
    }

    public function getAuthRoleBySlug(string $authRoleSlug) {
        $roleData = $this->authRoleRepository->findBySlug($authRoleSlug);

        return $roleData;
    }

    private function existsBySlug(string $authRoleSlug, ?string $ignoreId = null): void {
        if ($this->authRoleRepository->existsBySlug($authRoleSlug, $ignoreId)) {
            throw new ValidationFormRequestException(context: [
                'auth_role_slug' => __('validation.unique', ['attribute' => __('attributes.auth_role_slug')]),
            ]);
        }
    }
}

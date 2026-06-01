<?php

namespace App\Services\Applications;

use App\Enums\{AppAuthResponseCode, TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum, TypeUpdateEnum};
use App\Exceptions\{AppAuthException, ValidationFormRequestException};
use App\Models\AuthPermission;
use App\Repositories\Interfaces\AuthPermissionRepositoryInterface;
use App\Services\BaseApplicationService;
use App\Traits\LogAudit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AuthPermissionService extends BaseApplicationService {
    use LogAudit;

    public function __construct(
        protected AuthPermissionRepositoryInterface $authPermissionRepository
    ) {}

    public function listAuthPermissions(?string $search, int $perPage = 10, int $typeList = TypeBrowseEnum::WITHOUT_DELETED): LengthAwarePaginator {
        $this->validationQueryParamsTypeList($typeList);

        return $this->authPermissionRepository->paginateWithSearchAndType($search, $perPage, $typeList);
    }

    public function createAuthPermission(array $data): AuthPermission {
        //* Check Exist permission slug
        $this->existsBySlug($data['auth_permission_slug']);

        //* Check exist permission order
        $this->existsByPermissionOrder($data);

        return $this->authPermissionRepository->create($data);
    }

    public function getAuthPermissionById(string $authPermissionId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, array $relations = []): ?AuthPermission {
        $this->validationQueryParamsTypeRead($typeRead);

        try {
            $dataPermission = $this->authPermissionRepository->findById($authPermissionId, $typeRead, true, $relations);

            return $dataPermission;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new AppAuthException(AppAuthResponseCode::NotFound);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function updateAuthPermission(string $authPermissionId, array $data, int $typeUpdate = TypeUpdateEnum::WITHOUT_DELETED): AuthPermission {
        $this->validationQueryParamsTypeUpdate($typeUpdate);

        try {
            $permission = $this->authPermissionRepository->findById($authPermissionId, $typeUpdate, true);

            //* Check Exist permission slug
            $this->existsBySlug($data['auth_permission_slug'], $authPermissionId);

            //* Check exist permission order
            $this->existsByPermissionOrder($data, $authPermissionId);

            return $this->authPermissionRepository->update($permission, $data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new AppAuthException(AppAuthResponseCode::NotFound);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function deleteAuthPermission(string $authPermissionId, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): array {
        $this->validationQueryParamsTypeDelete($typeDelete);

        //* Determine readType based on typeDelete
        $typeRead = $this->DetermineTypeReadBaseOnTypeDelete($typeDelete);

        try {
            //* Find record by typeRead
            $permission = $this->authPermissionRepository->findById($authPermissionId, $typeRead);

            $this->authPermissionRepository->delete($permission, $typeDelete);

            //* Mapping message from typeDelete
            $message = $this->MappingMessageTypeDelete($typeDelete);

            return compact('message');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new AppAuthException(AppAuthResponseCode::NotFound);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function optionPermissions(?string $search, int $perPage = 10, int $typeList = TypeBrowseEnum::WITHOUT_DELETED, array $filterWhereIn = []): LengthAwarePaginator {
        $this->validationQueryParamsTypeList($typeList);

        return $this->authPermissionRepository->paginateWithSearchAndType($search, $perPage, $typeList, $filterWhereIn);
    }

    public function getAllPermissionActive() {
        return $this->authPermissionRepository->getAllPermissionsActive();
    }

    private function existsBySlug(string $authPermissionSlug, ?string $ignoreId = null): void {
        if ($this->authPermissionRepository->existsBySlug($authPermissionSlug, $ignoreId)) {
                throw new ValidationFormRequestException(context: [
                    'auth_permission_slug' => __('validation.unique', ['attribute' => __('attributes.auth_permission_slug')]),
                ]);
            }
    }

    private function existsByPermissionOrder(array $data, ?string $ignoreId = null): void {
        if ($this->authPermissionRepository->existsPermissionOrder($data, $ignoreId)) {
                throw new ValidationFormRequestException(context: [
                    'auth_permission_order' => __('validation.unique', ['attribute' => __('attributes.auth_permission_order')]),
                ]);
            }
    }
}

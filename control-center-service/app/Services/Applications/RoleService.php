<?php

namespace App\Services\Applications;

use App\Enums\{AppAuthResponseCode, TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum, TypeUpdateEnum};
use App\Exceptions\{AppControlCenterException, ValidationFormRequestException};
use App\Services\BaseApplicationService;
use App\Services\Clients\AuthRoleClient;
use Symfony\Component\HttpFoundation\Response;

class RoleService extends BaseApplicationService {

    /**
     * Create a new class instance.
     */
    public function __construct(
        protected AuthRoleClient $authRoleClient
    ) {}

    public function listRoles(?string $search, int $perPage, int $typeList = TypeBrowseEnum::WITHOUT_DELETED): array {
        //* 1. Check Validation Query Params
        $this->validationQueryParamsTypeList($typeList);

        //* 2. Hit Authentication Role from Auth Service secara synchronous
        $authRoleResponse = $this->authRoleClient->getListAuthRole([
            'q' => $search,
            'limit' => $perPage,
            'type_list' => $typeList
        ]);

        //* 3. Check validation response internal Auth Service
        $this->validateAuthRoleResponse($authRoleResponse);

        return $authRoleResponse['data'];
    }

    public function createRole(array $payloads): array {
        try {
            //* 1. Hit Auth Service secara Synchronous
            $authRoleResponse = $this->authRoleClient->storeAuthRole($payloads);

            //* 2. Check validation response internal Auth Service
            $this->validateAuthRoleResponse($authRoleResponse);

            return $authRoleResponse['data'];
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getRoleById(string $authRoleId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, array $relations = []): ?array {
        $this->validationQueryParamsTypeRead($typeRead);

        try {
            //* 1. Hit Authentication Role from Auth Service secara synchronous
            $authRoleResponse = $this->authRoleClient->getAuthRole($authRoleId, ['type_read' => $typeRead]);
            // dd($authRoleResponse);

            //* 2. Check validation response internal Auth Service
            $this->validateAuthRoleResponse($authRoleResponse);

            return $authRoleResponse['data'];
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function updateRole(string $authRoleId, array $payloads, int $typeUpdate = TypeUpdateEnum::WITHOUT_DELETED): array {
        $this->validationQueryParamsTypeUpdate($typeUpdate);

        try {
            //* Hit Auth Service secara Synchronous
            $authRoleResponse = $this->authRoleClient->updateAuthRole($authRoleId, $payloads, ['type_update' => $typeUpdate]);

            //* Check validation response internal Auth Service
            $this->validateAuthRoleResponse($authRoleResponse);

            return $authRoleResponse['data'];
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function deleteRole(string $authRoleId, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): array {
        $this->validationQueryParamsTypeDelete($typeDelete);

        try {
            //* Hit Auth Service secara Synchronous
            $authRoleResponse = $this->authRoleClient->destroyAuthRole($authRoleId, [
                'type_delete' => $typeDelete
            ]);

            //* Check validation response internal Auth Service
            $this->validateAuthRoleResponse($authRoleResponse);

            //* Mapping message from typeDelete
            $message = $this->MappingMessageTypeDelete($typeDelete);

            return compact('message');
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function optionPermissions(?string $search): array {
        //* 1. Hit Authentication Role from Auth Service secara synchronous
        $authPermissionResponse = $this->authRoleClient->getOptionsAuthPermission([
            'q' => $search
        ]);

        //* 2. Check validation response internal Auth Service
        $this->validateAuthRoleResponse($authPermissionResponse);

        return $authPermissionResponse['data'];
    }

    /**
     * Validate response from Auth Service
     */
    private function validateAuthRoleResponse(?array $response): void {
        //* Check Service Availability (Null or Fallback)
        if (!$response || ($response['fallback'] ?? false))
            throw new AppControlCenterException(AppAuthResponseCode::ServiceUnavailable);

        $status = $response['status'] ?? null;
        $message = $response['message'] ?? null;

        //* Check if client error statusCode >= 400 && statusCode < 500
        if ($status !== 404 && ($status >= 400 && $status < 500))
            throw new ValidationFormRequestException(context: $response['errors'] ?? []);

        //* Check if client error statusCode = 404
        if ($status === 404)
            throw new AppControlCenterException(codeName: AppAuthResponseCode::NotFound);

        //* Check status code valid & message valid
        if (!$this->isValidStatusCode($status) || !$this->isValidMessage($message))
            throw new AppControlCenterException(AppAuthResponseCode::InvalidServiceResponse);
    }

    private function isValidStatusCode(?int $statusCode): bool {
        return in_array($statusCode, [
            Response::HTTP_CREATED,
            Response::HTTP_OK
        ], true);
    }

    /**
     * Cek apakah message response termasuk dalam kategori sukses yang diizinkan
     */
    private function isValidMessage(?string $message): bool {
        return in_array($message, [
            AppAuthResponseCode::SuccessCreate->value,
            AppAuthResponseCode::SuccessRetrieveData->value,
            AppAuthResponseCode::SuccessUpdate->value,
            AppAuthResponseCode::SuccessDelete->value,
            AppAuthResponseCode::SuccessSoftDelete->value,
            AppAuthResponseCode::SuccessRestoreDelete->value,
            AppAuthResponseCode::SuccessDeleteFromTrash->value,
            AppAuthResponseCode::SuccessHardDelete->value
        ], true);
    }
}

<?php

namespace App\Services\Applications;

use App\Enums\{AppAuthResponseCode, TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum, TypeUpdateEnum};
use App\Exceptions\{AppControlCenterException, ValidationFormRequestException};
use App\Models\User;
use App\Repositories\Interfaces\{CompanyRepositoryInterface, UserRepositoryInterface};
use App\Services\BaseApplicationService;
use App\Services\Clients\AuthUserClient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};
use Symfony\Component\HttpFoundation\Response;

class UserService extends BaseApplicationService {

    /**
     * Create a new class instance.
     */
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected CompanyRepositoryInterface $companyRepository,
        protected AuthUserClient $authUserClient,
    ) {}

    public function listUsers(?string $search, int $perPage, int $typeList = TypeBrowseEnum::WITHOUT_DELETED): LengthAwarePaginator {
        $this->validationQueryParamsTypeList($typeList);

        return $this->userRepository->paginateWithSearchAndType($search, $perPage, $typeList);
    }

    public function createUser(array $payloads): User {
        $authUserId = null;

        try {
            //* 1. Hit Auth Service secara Synchronous
            $authUserResponse = $this->authUserClient->storeAuthUser($payloads['auth_user']);

            //* 2. Check validation response internal Auth Service
            $this->validateAuthUserResponse($authUserResponse);

            $authUserId = $authUserResponse['data']['auth_user_id'];
            $payloads['user']['user_auth_user_id'] = $authUserId;

            //* 3. Check validation unique Profile User
            $this->validationUnique($payloads);

            //* 4. Insert profil user
            $user = DB::transaction(function () use ($payloads) {
                return $this->userRepository->createUser($payloads['user']);
            });

            $user->setRelation('authUser', (object) $authUserResponse['data']);

            return $user;
        } catch (AppControlCenterException $ex) {
            //* 5. Compensating Transaction (ROLLBACK)
            if (!empty($authUserId))
                $this->authUserClient->destroyAuthUser($authUserId, ['type_delete' => 4]);

            throw $ex;
        } catch (\Exception $ex) {
            //* 5. Compensating Transaction (ROLLBACK)
            if (!empty($authUserId))
                $this->authUserClient->destroyAuthUser($authUserId, ['type_delete' => 4]);

            Log::error("User creation failed. Rolled back Auth Service ID: $authUserId. Error: " . $ex->getMessage());

            throw $ex;
        }
    }

    public function getUserById(string $userId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, array $relations = []): ?User {
        $this->validationQueryParamsTypeRead($typeRead);

        try {
            //* 1. Find profile User
            $findData = $this->userRepository->findById($userId, $typeRead, true, $relations);
            if (!$findData)
                throw new AppControlCenterException(AppAuthResponseCode::NotFound);

            $authUserId = $findData?->user_auth_user_id;

            //* 2. Hit Authentication User from Auth Service secara synchronous
            $authUserResponse = $this->authUserClient->getAuthUser($authUserId, ['type_read' => $typeRead]);

            //* 3. Check validation response internal Auth Service
            $this->validateAuthUserResponse($authUserResponse);

            $authData = $authUserResponse['data'];
            $roleData = $authData['role'] ?? null;
            unset($authData['role']);

            //* 4 Find Company
            $companyId = $authData['auth_user_company_id'];
            $findDataCompany = $companyId ? $this->companyRepository->findById($companyId) : null;

            //* 5. Set manual relationship
            $findData->setRelation('authUser', (object) $authData);
            $findData->setRelation('authRole', (object) $roleData);
            $findData->setRelation('company', $findDataCompany);

            return $findData;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function updateUser(string $userId, array $payloads, int $typeUpdate = TypeUpdateEnum::WITHOUT_DELETED): User {
        $this->validationQueryParamsTypeUpdate($typeUpdate);

        $findData = null;
        $oldData = [];
        $authUserId = null;

        try {
            //* Find data profile user by user_id
            $findData = $this->userRepository->findById($userId, $typeUpdate, true);
            if (!$findData)
                throw new AppControlCenterException(AppAuthResponseCode::NotFound);

            $oldData = $findData->getAttributes();
            $payloads['user']['user_auth_user_id'] = $findData?->user_auth_user_id;

            //* Check validation unique Profile User
            $this->validationUnique($payloads, $userId);

            //* Update profile user
            $user = DB::transaction(function () use ($findData, $payloads) {
                return $this->userRepository->updateUser($findData, $payloads['user']);
            });

            //* Hit Auth Service secara Synchronous
            $authUserId = $user?->user_auth_user_id;
            $authUserResponse = $this->authUserClient->updateAuthUser($authUserId, $payloads['auth_user'], ['type_update' => $typeUpdate]);

            //* Check validation response internal Auth Service
            $this->validateAuthUserResponse($authUserResponse);

            //* Manual set relation to model users
            $user->setRelation('authUser', (object) $authUserResponse['data']);

            return $user;
        } catch (\Exception $ex) {
            //* Compensating Transaction (ROLLBACK)
            if ($authUserId && !empty($oldData)) {
                Log::warning("Auth Service update failed. Rolling back local changes for User ID: {$userId}.", ['message' => $ex->getMessage()]);

                //* Rollback profile user
                DB::transaction(function () use ($oldData) {
                    return $this->userRepository->rollbackToOldData($oldData);
                });
            }

            throw $ex;
        }
    }

    public function deleteUser(string $userId, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): array {
        $this->validationQueryParamsTypeDelete($typeDelete);

        //* Determine readType based on typeDelete
        $typeRead = $this->DetermineTypeReadBaseOnTypeDelete($typeDelete);

        $findData = null;
        $oldData = [];
        $authUserId = null;

        try {
            $findData = $this->userRepository->findById($userId, $typeRead);
            if (!$findData)
                throw new AppControlCenterException(AppAuthResponseCode::NotFound);

            $oldData = $findData->getAttributes();

            //* Delete profile user
            $user = DB::transaction(function () use ($findData, $typeDelete) {
                $this->userRepository->delete($findData, $typeDelete);

                //* Mapping message from typeDelete
                $message = $this->MappingMessageTypeDelete($typeDelete);

                return compact('message');
            });

            //* Hit Auth Service secara Synchronous
            $authUserId = $findData?->user_auth_user_id;
            $authUserResponse = $this->authUserClient->destroyAuthUser($authUserId, [
                'type_delete' => $typeDelete
            ]);

            //* Check validation response internal Auth Service
            $this->validateAuthUserResponse($authUserResponse);

            return $user;
        } catch (\Exception $ex) {
            //* Compensating Transaction (ROLLBACK LOKAL)
            if (!empty($oldData)) {
                Log::warning("Auth Service failed. Rolling back local changes for User ID: $userId", ['message' => $ex->getMessage()]);

                DB::transaction(function () use ($oldData) {
                    return $this->userRepository->rollbackToOldData($oldData);
                });
            }

            throw $ex;
        }
    }

    public function optionRoles(?string $search): array {
        //* 1. Hit Authentication Role from Auth Service secara synchronous
        $authPermissionResponse = $this->authUserClient->getOptionsAuthRole([
            'q' => $search
        ]);

        //* 2. Check validation response internal Auth Service
        $this->validateAuthUserResponse($authPermissionResponse);

        return $authPermissionResponse['data'];
    }

    private function validationUnique(array $payloads, ?string $ignoreId = null): void {
        if ($this->userRepository->existsByAuthUserId($payloads['user']['user_auth_user_id'], $ignoreId)) {
            throw new ValidationFormRequestException(context: [
                'user.user_auth_user_id' => __('validation.unique', ['attribute' => __('attributes.user.user_auth_user_id')]),
            ]);
        }

        if ($this->userRepository->existsByPhone($payloads['user']['user_phone'], $ignoreId)) {
            throw new ValidationFormRequestException(context: [
                'user.user_phone' => __('validation.unique', ['attribute' => __('attributes.user.user_phone')]),
            ]);
        }
    }

    /**
     * Validate response from Auth Service
     */
    private function validateAuthUserResponse(?array $response): void {
        //* Check Service Availability (Null or Fallback)
        if (!$response || ($response['fallback'] ?? false))
            throw new AppControlCenterException(AppAuthResponseCode::ServiceUnavailable);

        $status = $response['status'] ?? null;
        $message = $response['message'] ?? null;

        //* Check if client error statusCode >= 400 && statusCode < 500
        if ($status >= 400 && $status < 500)
            throw new ValidationFormRequestException(context: $response['errors'] ?? []);

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

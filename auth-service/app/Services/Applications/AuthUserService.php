<?php

namespace App\Services\Applications;

use App\Enums\{AppAuthResponseCode, TypeDeleteEnum, TypeReadEnum, TypeUpdateEnum};
use App\Exceptions\{AppAuthException, ValidationFormRequestException};
use App\Models\AuthUser;
use App\Repositories\Interfaces\AuthUserRepositoryInterface;
use App\Services\{BaseApplicationService, UserCacheService};
use App\Services\Clients\ControlCenterServiceClient;
use Illuminate\Support\Facades\DB;

class AuthUserService extends BaseApplicationService {

    public function __construct(
        protected AuthUserRepositoryInterface $authUserRepository,
        protected ControlCenterServiceClient $controlCenterService,
        protected AuthRoleService $authRoleService,
        protected UserCacheService $userCache,
        protected string $localeMessage = 'en',
    ) {}

    /**
     * Create new user
     *
     * Flow:
     * 1. Get role by slug (default: admin) if role_id not provided
     * 2. Create auth user in Auth Service
     * 3. Create company + user in Control Center Service
     * 4. Validate response from Control Center Service
     * 5. Update auth_user_company_id in Auth Service
     * 6. Dispatch welcome email job to Redis queue
     *
     * @param array $authUserData create data
     * @return AuthUser
     */
    public function createAuthUser(array $authUserData): AuthUser {
        $this->validationUnique($authUserData);

        try {
            /*
            //* Step 1: Get role - use provided role_id or get default "admin" role
            $creator = Auth::user();
            if (!$creator || !$creator->auth_user_is_admin) {
                $role = $this->authRoleService->getAuthRoleBySlug('admin');
                if (!$role)
                    throw new AppAuthException(codeName: AppAuthResponseCode::RoleNotFound);

                $authUserData['auth_user_role_id'] = $role->auth_role_id;
            }
            */

            //* Step 2: Create auth user in Auth Service
            $authUser = DB::transaction(function () use ($authUserData) {
                return $this->authUserRepository->create($authUserData);
            });
            // $authUserId = $authUser->getKey();

            /*
            //* Step 3: Create company + user in Control Center Service
            $userData['user_auth_user_id'] = $authUserId;
            $controlCenterResponse = $this->controlCenterService->createCompanyWithUser([
                'company' => $companyData,
                'user' => $userData
            ]);

            //* Step 4: Validate Control Center response
            $this->validateControlCenterResponse($controlCenterResponse);
            $controlCenterCompanyId = $controlCenterResponse['data']['company']['company_id'] ?? null;

            //* Step 5: Update auth user dengan company_id
            DB::transaction(function () use ($authUser, $controlCenterCompanyId) {
                $authUser->auth_user_company_id = $controlCenterCompanyId;

                $authUser->save();
            });

            //* Step 6: Dispatch verification email company job (outside transaction – intentional)
            SendCompanyVerificationEmailJob::dispatch(
                $authUser,
                $controlCenterResponse['data']['company'],
                $controlCenterResponse['data']['user']
            )->onQueue('emails');

            //* Step 7: Dispatch verification email user job (outside transaction – intentional)
            SendUserVerificationEmailJob::dispatch(
                $authUser,
                $controlCenterResponse['data']['company'],
                $controlCenterResponse['data']['user'],
            )->onQueue('emails');

            return [
                'auth_user' => $authUser,
                'company' => $controlCenterResponse['data']['company'],
                'user' => $controlCenterResponse['data']['user'],
            ];
            */

            return $authUser;
        } catch (AppAuthException $e) {
            //* If an error occurs after step 2 is successful, delete it.
            if (isset($controlCenterResponse) && ($controlCenterResponse['status'] ?? null) === 201) {
                $controlCenterCompanyId = $controlCenterResponse['data']['company']['company_id'] ?? null;
                $controlCenterAuthUserId = $controlCenterResponse['data']['user']['user_auth_user_id'] ?? null;

                //* Delete company + user in Control Center Service
                $this->controlCenterService->destroyCompanyWithUser($controlCenterCompanyId, $controlCenterAuthUserId);
            }

            //* If auth user has been created, Delete it.
            if (isset($authUser))
                $authUser->delete();

            throw $e;
        } catch (\Exception $e) {
            //* If an error occurs after step 2 is successful, delete it.
            if (isset($controlCenterResponse) && ($controlCenterResponse['status'] ?? null) === 201) {
                $controlCenterCompanyId = $controlCenterResponse['data']['company']['company_id'] ?? null;
                $controlCenterAuthUserId = $controlCenterResponse['data']['user']['user_auth_user_id'] ?? null;

                //* Delete company + user in Control Center Service
                $this->controlCenterService->destroyCompanyWithUser($controlCenterCompanyId, $controlCenterAuthUserId);
            }

            //* If auth user has been created, Delete it.
            if (isset($authUser))
                $authUser->delete();

            throw $e;
        }
    }

    public function getAuthUserById(string $authUserId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, array $relations = []): ?AuthUser {
        $this->validationQueryParamsTypeRead($typeRead);

        try {
            return $this->authUserRepository->findById($authUserId, $typeRead, true, $relations);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new AppAuthException(AppAuthResponseCode::NotFound);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function updateAuthUser(string $authUserId, array $payload, int $typeUpdate = TypeUpdateEnum::WITHOUT_DELETED): AuthUser {
        $this->validationQueryParamsTypeUpdate($typeUpdate);

        try {
            //* Find data auth user by auth_user_id
            $findData = $this->authUserRepository->findById($authUserId, $typeUpdate, true);

            //* Check existing username and email
            // $this->validationUnique($payload['auth_role_slug'], $authUserId);
            $this->validationUnique($payload, $authUserId);

            return DB::transaction(function () use ($findData, $payload) {
                //* Update Auth Users
                return $this->authUserRepository->update($findData, $payload);
            });
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new AppAuthException(AppAuthResponseCode::NotFound);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function deleteAuthUser(string $authUserId, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): array {
        $this->validationQueryParamsTypeDelete($typeDelete);

        //* Determine readType based on typeDelete
        $typeRead = $this->DetermineTypeReadBaseOnTypeDelete($typeDelete);

        try {
            $findData = $this->authUserRepository->findById($authUserId, $typeRead);

            return DB::transaction(function () use ($findData, $typeDelete) {
                //* Delete table Auth Users
                $this->authUserRepository->delete($findData, $typeDelete);

                //* Mapping message from typeDelete
                $message = $this->MappingMessageTypeDelete($typeDelete);

                //* Send sync Auth Users to Control Center Service
                // $data->sync('delete');

                return compact('message');
            });
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new AppAuthException(AppAuthResponseCode::NotFound);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function validationUnique(array $authUserData, ?string $ignoreId = null): void {
        if ($this->authUserRepository->existsByEmail($authUserData['auth_user_email'], $ignoreId)) {
            throw new ValidationFormRequestException(context: [
                'auth_user.auth_user_email' => __('validation.unique', ['attribute' => __('attributes.auth_user.auth_user_email')]),
            ]);
        }

        if ($this->authUserRepository->existsByUsername($authUserData['auth_user_username'], $ignoreId)) {
            throw new ValidationFormRequestException(context: [
                'auth_user.auth_user_username' => __('validation.unique', ['attribute' => __('attributes.auth_user.auth_user_username')]),
            ]);
        }
    }

}

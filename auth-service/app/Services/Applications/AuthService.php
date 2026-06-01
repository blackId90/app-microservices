<?php

namespace App\Services\Applications;

use App\Enums\AppAuthResponseCode;
use App\Exceptions\AccountBlockedException;
use App\Exceptions\AppAuthException;
use App\Exceptions\JWTAuthException;
use App\Exceptions\UserNotFoundFromTokenException;
use App\Repositories\Interfaces\AuthUserRepositoryInterface;
use App\Services\Clients\ProfileServiceClient;
use App\Services\JwtRedisService;
use App\Services\PermissionAggregatorService;
use App\Services\PermissionCacheService;
use App\Services\RoleCacheService;
use App\Services\RolePermissionCacheService;
use App\Services\UserCacheService;
use App\Traits\LogAudit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService {
    use LogAudit;

    public function __construct(
        protected JwtRedisService $jwtRedis,
        protected UserCacheService $userCache,
        protected RoleCacheService $roleCache,
        protected PermissionCacheService $permissionCache,
        protected RolePermissionCacheService $rolePermissionCache,
        protected PermissionAggregatorService $permissionAggregatorService,
        protected AuthUserRepositoryInterface $userRepo,
        protected LoginAttemptService $loginAttemptService,
        protected ProfileServiceClient $profileServiceClient,
        protected string $localeMessage = 'en',
    ) {
    }

    public function login(array $credentials) {
        //* Query DB cari user dengan join role by identifier (email/username, password)
        $user = $this->userRepo->findByIdentifier($credentials['auth_user_email']);

        //* Check user = null, 404 - user not found
        if (!$user) {
            $errorCode = AppAuthResponseCode::AccountNotFound;
            $errorMessage = $errorCode->getMessage(locale: $this->localeMessage);

            //* Store login attempts
            $this->loginAttemptService->createLog($credentials, $errorMessage);

            throw new AppAuthException($errorCode);
        }

        //* Verification password, password = false, 401 - invalid credentials
        if (!Hash::check($credentials['password'], $user->auth_user_password)) {
            $errorCode = AppAuthResponseCode::InvalidCredential;
            $errorMessage = $errorCode->getMessage(locale: $this->localeMessage);

            //* Store login attempts
            $this->loginAttemptService->createLog($credentials, $errorMessage);

            throw new AppAuthException($errorCode);
        }

        //* Check status user auth_user_email_verified_at = null && auth_user_is_status = -1, 403 - email not verified
        if (!$user->auth_user_email_verified_at || $user->auth_user_is_status === -1) {
            $errorCode = AppAuthResponseCode::EmailNotVerified;
            $errorMessage = $errorCode->getMessage(locale: $this->localeMessage);

            //* Store login attempts
            $this->loginAttemptService->createLog($credentials, $errorMessage);

            throw new AppAuthException($errorCode);
        }

        //* Check status user auth_user_is_status = 0, 403 - account suspended
        if ($user->auth_user_is_status === 0) {
            $errorCode = AppAuthResponseCode::AccountBlocked;
            $errorMessage = $errorCode->getMessage(locale: $this->localeMessage);

            //* Store login attempts
            $this->loginAttemptService->createLog($credentials, $errorMessage);

            throw new AccountBlockedException();
        }

        //* Get role
        $role = $this->roleCache->getRole($user->auth_user_role_id);

        //* Check status role = inactive, 403 - role inactive
        if ($role && !$role?->auth_role_is_active) {
            $errorCode = AppAuthResponseCode::RoleInactive;
            $errorMessage = $errorCode->getMessage(locale: $this->localeMessage);

            //* Store login attempts
            $this->loginAttemptService->createLog($credentials, $errorMessage);

            throw new AppAuthException($errorCode);
        }

        //* Communication internal service control center
        $this->controlCenterService($credentials, $user);

        //* Check permissions
        $this->getPermissions($credentials, $user);

        //* Check role permissions
        $this->getRolePermissions($credentials, $user);

        //* Validations success, generate token, and store login attempts
        try {
            return DB::transaction(function () use ($user, $credentials) {
                $errorMessage = AppAuthResponseCode::LoginSuccess->getMessage(type: 'success', locale: $this->localeMessage);

                //* Store login attempts
                $this->loginAttemptService->createLog($credentials, $errorMessage, true, $user->auth_user_id);

                //* Generate final token with custom claims from model
                $customClaims = $user->getJWTCustomClaims();

                //* Make sure exp is not overridden in custom claims
                unset($customClaims['temp'], $customClaims['scope'], $customClaims['purpose'], $customClaims['exp']);

                //* Claims final token
                $customClaims['temp'] = false;
                $customClaims['scope'] = 'full_access';
                $customClaims['purpose'] = 'auth';

                //* Generate final token using JWTAuth facade
                $token = JWTAuth::customClaims($customClaims)->fromUser($user); // $token = JWTAuth::fromUser($user);
                $expiresIn = $this->jwtRedis->getExpiresIn($token);

                //* Reset client to use final token or request token
                $this->profileServiceClient->reset();
                $this->profileServiceClient->updateTokenFromRequest();

                //* Get latest login
                $latestLogin = $this->loginAttemptService->getLatestLogin($user->auth_user_id);
                $user->setRelation('latestLoginAttempt', $latestLogin);

                //* Store token in Redis (this will invalidate previous sessions - single sign-in)
                $this->jwtRedis->storeUserToken($user->getKey(), $token);

                //* Refresh user cache
                $this->userCache->cacheUser($user);

                return [
                    'token' => $token,
                    'expires_in' => $expiresIn,
                    'user' => $user,
                    // 'permissions' => $permissions,
                ];
            });
        } catch (JWTException) {
            throw new JWTAuthException(AppAuthResponseCode::CouldNotCreateToken);
        }
    }

    public function logout() {
        $userRequest = request()->user();
        if (!$userRequest)
            throw new UserNotFoundFromTokenException();

        $userId = $userRequest['auth_user_id'];

        //* Get control center data (remote control center service)
        $this->profileServiceClient->destroyProfileDataControlCenter();

        //* Delete the user's active token from Redis
        $this->jwtRedis->deleteUserToken($userId);

        //* Invalidate user cache (only main cache key, no need for DB query)
        $this->userCache->invalidateUserCacheOnly($userId);

        //* Optionally, invalidate the JWT token globally (if needed)
        // JWTAuth::invalidate(JWTAuth::getToken());

        return true;
    }

    public function signinProfile() {
        $userRequest = request()->user();
        if (!$userRequest)
            throw new UserNotFoundFromTokenException();

        //* Get session info
        $userId = $userRequest['auth_user_id'];
        $roleId = isset($userRequest['auth_user_role_id']) ? $userRequest['auth_user_role_id'] : null;
        if (!$roleId)
            throw new AppAuthException(codeName: AppAuthResponseCode::RoleNotFound);

        //* Get role
        $role = $this->roleCache->getRole($roleId);

        //* Check status role = inactive, 403 - role inactive
        if ($role && !$role?->auth_role_is_active)
            throw new AppAuthException(codeName: AppAuthResponseCode::RoleInactive);

        $sessionInfo = $this->jwtRedis->getUserSessionInfo($userId);

        //* Get control center data (remote control center service)
        $controlCenterData = $this->profileServiceClient->getProfileDataControlCenter();

        //* Check service availability
        $serviceAvailable = !isset($controlCenterData['fallback']);
        $isHasProfileUser = $serviceAvailable && isset($controlCenterData['data']['user']);
        $isHasProfileCompany = $serviceAvailable && isset($controlCenterData['data']['company']);

        return [
            'isCache' => $this->userCache->isCached($userId),
            'session' => $sessionInfo,
            'services' => [
                'auth_user' => $userRequest,
                'profile_user' => $isHasProfileUser ? $controlCenterData['data']['user'] : null,
                'role' => $role,
                'company' => $isHasProfileCompany ? $controlCenterData['data']['company'] : null,
            ],
        ];
    }

    public function signinMenus() {
        //* Get user from request
        $userRequest = request()->user();

        //* Get user permissions
        $userPermissions = $this->permissionAggregatorService->getPermissionsForApi($userRequest->auth_user_role_id);
        if ($userPermissions->isEmpty()) {
            $errorMessage = 'Not set permissions in role';

            Log::warning($errorMessage, [
                'user_id' => $userRequest->auth_user_id,
                'user_role_id' => $userRequest->auth_user_role_id,
                'user_company_id' => $userRequest->auth_user_company_id,
            ]);

            $errorCode = AppAuthResponseCode::Forbidden;
            // $errorMessage = $errorCode->getMessage(locale: $this->localeMessage);

            throw new AppAuthException(
                codeName: $errorCode,
                context: LogAudit::setContexLog(isLog: true, level: 'warning', message: "Forbidden: {$errorMessage}")
            );
        }

        return $userPermissions;
    }

    public function hasAccessPermission(Request $request) {
        //* Extract request user info
        $userId = $request->attributes->get('userId', null);
        $roleId = $request->attributes->get('roleId', null);
        if (!$userId || !$roleId)
            throw new AppAuthException(AppAuthResponseCode::Unauthorized);

        try {
            $requestPermissionSlug = $request->input('request_route_name');
            $requestParameter = $request->input('request_parameter');
            $serviceName = $request->header('X-Request-Source', 'unknown');

            //* Check permission with cache
            $hasPermission = $this->permissionAggregatorService->hasPermission($roleId, $requestPermissionSlug);

            //* Get permission paramater
            $parameter = $hasPermission ? $this->permissionAggregatorService->getPermissionParameter($roleId, $requestPermissionSlug) : null;
            // dd($requestPermissionSlug, $routeName, $serviceName, $hasPermission, $parameter);
            return [
                'user_id' => $userId,
                'role_id' => $roleId,
                'has_permission' => $hasPermission,
                'permission_parameter' => $parameter,
                'request_route_name' => $requestPermissionSlug,
                'request_parameter' => $requestParameter,
                'request_source_service' => $serviceName,
                'checked_at' => now()->toISOString(),
            ];
        } catch (\Exception $ex) {
            /*
            Log::error('Permission check failed', [
                'error' => $ex->getMessage(),
                'trace' => $ex->getTraceAsString(),
                'permission_slug' => $request->input('permission_slug'),
                'request_source_service' => $request->header('X-Request-Source'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'data' => null
            ], 500);
            */

            throw $ex;
        }
    }

    public function bannedToken(string $userId) {
        $result = $this->jwtRedis->verifyAndBanToken($userId);
        if ($result)
            throw new AppAuthException($result);
    }

    public function unbannedToken(string $userId) {
        $result = $this->jwtRedis->verifyAndUnbanToken($userId);
        if ($result)
            throw new AppAuthException($result);
    }

    private function getPermissions(array $credentials, $user): void {
        $permissions = $this->permissionCache->getAllPermissions();
        if ($permissions->isEmpty()) {
            $errorMessage = 'Not set permissions in role';

            Log::warning($errorMessage, [
                'user_id' => $user->auth_user_id,
                'user_role_id' => $user->auth_user_role_id,
                'user_company_id' => $user->auth_user_company_id,
            ]);

            $errorCode = AppAuthResponseCode::Unauthorized;
            $errorCodeMessage = $errorCode->getMessage(locale: $this->localeMessage);

            //* Store login attempts
            $this->loginAttemptService->createLog($credentials, $errorCodeMessage);

            throw new AppAuthException(
                codeName: $errorCode,
                context: LogAudit::setContexLog(isLog: true, level: 'warning', message: "Unauthorized: {$errorMessage}")
            );
        }
    }

    private function getRolePermissions(array $credentials, $user): void {
        $rolePermissions = $this->rolePermissionCache->getRolePermissions($user->auth_user_role_id);
        if (empty($rolePermissions)) {
            $errorMessage = 'Not set role permissions in role';

            Log::warning($errorMessage, [
                'user_id' => $user->auth_user_id,
                'user_role_id' => $user->auth_user_role_id,
                'user_company_id' => $user->auth_user_company_id,
            ]);

            $errorCode = AppAuthResponseCode::Unauthorized;
            $errorCodeMessage = $errorCode->getMessage(locale: $this->localeMessage);

            //* Store login attempts
            $this->loginAttemptService->createLog($credentials, $errorCodeMessage);

            throw new AppAuthException(
                codeName: $errorCode,
                context: LogAudit::setContexLog(isLog: true, level: 'warning', message: "Unauthorized: {$errorMessage}")
            );
        }
    }

    private function controlCenterService(array $credentials, $authUser): void {
        try {
            //* Generate temp token
            $tempToken = $this->generateTempTokenForControlCenter($authUser);

            //* Reset the injected client to use the temp token
            $this->profileServiceClient->resetWithToken($tempToken);

            //* Get control center data with temp token (remote control center service)
            $controlCenterProfileData = $this->profileServiceClient->getProfileDataControlCenter();

            //* Check availability response from control center service
            $validationResultService = $this->validateControlCenterResponse($controlCenterProfileData, $authUser);
            if (!$validationResultService['valid']) {
                $errorCode = $validationResultService['error_code'];
                $errorMessage = $errorCode->getMessage(locale: $this->localeMessage);

                //* Store login attempts
                $this->loginAttemptService->createLog($credentials, $errorMessage);

                throw new AppAuthException($errorCode);
            }

            //* Validation data profile user from response control center service
            $validationResultUser = $this->validateControlCenterUserData($controlCenterProfileData, $authUser);
            if (!$validationResultUser['valid']) {
                $errorCode = $validationResultUser['error_code'];
                $errorMessage = $errorCode->getMessage(locale: $this->localeMessage);

                //* Store login attempts
                $this->loginAttemptService->createLog($credentials, $errorMessage);

                throw new AppAuthException($errorCode);
            }

            //* User non-administrator
            if (!$authUser->auth_user_is_admin) {
                //* Validation data company from response control center service
                $validationResultCompany = $this->validateControlCenterCompanyData($controlCenterProfileData, $authUser);
                if (!$validationResultCompany['valid']) {
                    $errorCode = $validationResultCompany['error_code'];
                    $errorMessage = $errorCode->getMessage(locale: $this->localeMessage);

                    //* Store login attempts
                    $this->loginAttemptService->createLog($credentials, $errorMessage);

                    throw new AppAuthException($errorCode);
                }
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Generate temp token for Control Center Service
     */
    private function generateTempTokenForControlCenter($user): string {
        try {
            //* Generate token for temp token
            $token = JWTAuth::fromUser($user);

            //* Decode dan modify for temp token
            $payload = JWTAuth::setToken($token)->getPayload();
            $claims = $payload->toArray();

            //* Added custom claims for temp token
            $claims['temp'] = true;
            $claims['scope'] = 'control_center_auth';
            $claims['purpose'] = 'validate_company';

            //* Set shorter expiry (1 minute)
            $claims['exp'] = now()->addMinutes(1)->timestamp;

            //* Generate new temp tokens with custom claims
            $tempToken = JWTAuth::customClaims($claims)->fromUser($user);

            return $tempToken;
        } catch (JWTException $e) {
            throw new JWTAuthException(AppAuthResponseCode::CouldNotCreateToken);
        }
    }

    /**
     * Validation response from Control Center Service
     */
    private function validateControlCenterResponse(?array $controlCenterResponse, $user): array {
        if (!$controlCenterResponse) {
            $errorMessage = 'No response from service control center';

            Log::warning($errorMessage, [
                'user_id' => $user->auth_user_id,
                'company_id' => $user->auth_user_company_id,
            ]);

            return [
                'valid' => false,
                'error_code' => AppAuthResponseCode::UnexpectedError,
                'message' => $errorMessage,
                'is_fallback' => false,
            ];
        }

        //* Check if the response is a fallback
        if (isset($controlCenterResponse['fallback']) && $controlCenterResponse['fallback'] === true) {
            $errorMessage = 'Control Center Service temporarily unavailable';

            Log::warning($errorMessage, [
                'user_id' => $user->auth_user_id,
                'company_id' => $user->auth_user_company_id,
            ]);

            return [
                'valid' => false,
                'error_code' => AppAuthResponseCode::ServiceUnavailable,
                'message' => $errorMessage,
                'is_fallback' => false,
                // 'is_fallback' => true, //! Tetap izinkan login dengan risk
            ];
        }

        //* Check response structure
        if (!isset($controlCenterResponse['status']) || $controlCenterResponse['status'] !== 200) {
            $errorMessage = 'Invalid response from Control Center Service';

            Log::warning($errorMessage, [
                'user_id' => $user->auth_user_id,
                'status' => $controlCenterResponse['status'] ?? 'unknown',
                'message' => $controlCenterResponse['message'] ?? 'No message',
            ]);

            return [
                'valid' => false,
                'error_code' => AppAuthResponseCode::InvalidServiceResponse,
                'message' => $errorMessage,
                'is_fallback' => false,
            ];
        }

        return [
            'valid' => true,
            'message' => 'Response service control center valid',
        ];
    }

    /**
     * Validation data profile user from Control Center Service
     */
    private function validateControlCenterUserData(?array $controlCenterResponse, $authUser): array {
        if (!isset($controlCenterResponse['data']['user']) || empty($controlCenterResponse['data']['user'])) {
            $errorMessage = 'No user data in Control Center response';

            Log::warning($errorMessage, [
                'auth_user_id' => $authUser->auth_user_id,
                'company_id' => $authUser->auth_user_company_id,
            ]);

            return [
                'valid' => false,
                'error_code' => AppAuthResponseCode::AccountNotFound,
                'message' => $errorMessage,
            ];
        }

        $userData = $controlCenterResponse['data']['user'];
        if ($userData['user_auth_user_id'] !== $authUser->auth_user_id) {
            $errorMessage = 'Auth User ID mismatch between Auth and Control Center';

            Log::warning($errorMessage, [
                'auth_user_id' => $authUser->auth_user_id,
                'company_id' => $authUser->auth_user_company_id,
                'control_center_user_id' => $userData['user_auth_user_id'] ?? null,
                'user_data' => $userData,
            ]);

            return [
                'valid' => false,
                'error_code' => AppAuthResponseCode::UserProfileMismatch,
                'message' => $errorMessage,
            ];
        }

        return [
            'valid' => true,
            'message' => 'Response user profile valid',
        ];
    }

    /**
     * Validation data company from Control Center Service
     */
    private function validateControlCenterCompanyData(?array $controlCenterResponse, $authUser): array {
        if (!isset($controlCenterResponse['data']['company']) || empty($controlCenterResponse['data']['company'])) {
            $errorMessage = 'No company data in Control Center response';

            Log::warning($errorMessage, [
                'auth_user_id' => $authUser->auth_user_id,
                'company_id' => $authUser->auth_user_company_id,
            ]);

            return [
                'valid' => false,
                'error_code' => AppAuthResponseCode::CompanyNotFound,
                'message' => $errorMessage,
            ];
        }

        $companyData = $controlCenterResponse['data']['company'];
        if ($companyData['company_id'] !== $authUser->auth_user_company_id) {
            $errorMessage = 'Company ID mismatch between Auth and Control Center';

            Log::warning($errorMessage, [
                'auth_user_id' => $authUser->auth_user_id,
                'company_id' => $authUser->auth_user_company_id,
                'control_center_user_id' => $companyData['company_id'] ?? null,
                'user_data' => $companyData,
            ]);

            return [
                'valid' => false,
                'error_code' => AppAuthResponseCode::CompanyMismatch,
                'message' => $errorMessage,
            ];
        }

        //* Check status company verification company_email_verified_at = null && company_is_status = -1, 403 - email not verified
        if (!$companyData['company_email_verified_at'] || $companyData['company_is_status'] === '-1') {
            $errorMessage = 'Company is pending email verification';

            Log::warning($errorMessage, [
                'auth_user_id' => $authUser->auth_user_id,
                'company_id' => $authUser->auth_user_company_id,
                'company_status' => $companyData['company_is_status'],
            ]);

            return [
                'valid' => false,
                'error_code' => AppAuthResponseCode::EmailNotVerified,
                'message' => $errorMessage,
            ];
        }

        //* Check status company inactive
        if ($companyData['company_is_status'] === '0') {
            $errorMessage = 'Company is inactive';

            Log::warning($errorMessage, [
                'auth_user_id' => $authUser->auth_user_id,
                'company_id' => $authUser->auth_user_company_id,
                'company_status' => $companyData['company_is_status'],
            ]);

            return [
                'valid' => false,
                'error_code' => AppAuthResponseCode::CompanyInactive,
                'message' => $errorMessage,
            ];
        }

        //* Check status billing company in (unpaid, expired, suspended)
        $invalidStatuses = ['unpaid', 'expired', 'suspended'];
        $billingStatus = $companyData['company_billing_status'];
        if (in_array($billingStatus, $invalidStatuses)) {
            //* Company status unpaid
            if ($billingStatus === 'unpaid') {
                $errorMessage = 'Company has unpaid invoices';

                Log::warning($errorMessage, [
                    'auth_user_id' => $authUser->auth_user_id,
                    'company_id' => $authUser->auth_user_company_id,
                    'company_status' => $billingStatus,
                ]);

                return [
                    'valid' => false,
                    'error_code' => AppAuthResponseCode::CompanyUnpaid,
                    'message' => $errorMessage,
                ];
            }

            //* Company status expired
            if ($billingStatus === 'expired') {
                $errorMessage = 'Company billing has expired';

                Log::warning($errorMessage, [
                    'auth_user_id' => $authUser->auth_user_id,
                    'company_id' => $authUser->auth_user_company_id,
                    'company_status' => $billingStatus,
                ]);

                return [
                    'valid' => false,
                    'error_code' => AppAuthResponseCode::CompanyExpired,
                    'message' => $errorMessage,
                ];
            }

            //* Company status suspended
            if ($billingStatus === 'suspended') {
                $errorMessage = 'Company is suspended';

                Log::warning($errorMessage, [
                    'auth_user_id' => $authUser->auth_user_id,
                    'company_id' => $authUser->auth_user_company_id,
                    'company_status' => $billingStatus,
                ]);

                return [
                    'valid' => false,
                    'error_code' => AppAuthResponseCode::CompanySuspended,
                    'message' => $errorMessage,
                ];
            }
        }

        //* Check status trial expired
        if ($billingStatus === 'trial') {
            $trialEndsAt = $companyData['company_trial_ends_at'] ?? null;
            if ($trialEndsAt && Carbon::parse($trialEndsAt)->isPast()) {
                $errorMessage = 'Company trial period has expired';

                Log::warning($errorMessage, [
                    'auth_user_id' => $authUser->auth_user_id,
                    'company_id' => $authUser->auth_user_company_id,
                    'company_status' => $billingStatus,
                    'trial_ends_at' => $trialEndsAt,
                ]);

                return [
                    'valid' => false,
                    'error_code' => AppAuthResponseCode::CompanyTrialExpired,
                    'message' => $errorMessage,
                ];
            }

            if (!$trialEndsAt) {
                $errorMessage = 'Company billing status is invalid';

                Log::warning($errorMessage, [
                    'auth_user_id' => $authUser->auth_user_id,
                    'company_id' => $authUser->auth_user_company_id,
                    'company_status' => $billingStatus,
                    'trial_ends_at' => $trialEndsAt,
                ]);

                return [
                    'valid' => false,
                    'error_code' => AppAuthResponseCode::CompanyBillingInvalid,
                    'message' => $errorMessage,
                ];
            }
        }

        //* Check status paid expired
        if ($billingStatus === 'paid') {
            $paidEndsAt = $companyData['company_paid_ends_at'] ?? null;
            if ($paidEndsAt && Carbon::parse($paidEndsAt)->isPast()) {
                $errorMessage = 'Company subscription has expired';

                Log::warning($errorMessage, [
                    'auth_user_id' => $authUser->auth_user_id,
                    'company_id' => $authUser->auth_user_company_id,
                    'company_status' => $billingStatus,
                    'paid_ends_at' => $paidEndsAt,
                ]);

                return [
                    'valid' => false,
                    'error_code' => AppAuthResponseCode::CompanyPaidExpired,
                    'message' => $errorMessage,
                ];
            }

            if (!$paidEndsAt) {
                $errorMessage = 'Company billing status is invalid';

                Log::warning($errorMessage, [
                    'auth_user_id' => $authUser->auth_user_id,
                    'company_id' => $authUser->auth_user_company_id,
                    'company_status' => $billingStatus,
                    'paid_ends_at' => $paidEndsAt,
                ]);

                return [
                    'valid' => false,
                    'error_code' => AppAuthResponseCode::CompanyBillingInvalid,
                    'message' => $errorMessage,
                ];
            }
        }

        //* Check configurations authentication company
        $checkAuthCompany = $this->validateControlCenterAuthCompanyData($companyData, $authUser);
        if (!$checkAuthCompany['valid'])
            return $checkAuthCompany;

        return [
            'valid' => true,
            'message' => 'Response company valid',
        ];
    }

    /**
     * Validation data configuration authentication company from Control Center Service
     *
     * @param array $companyData
     * @param [type] $authUser
     * @return array
     */
    private function validateControlCenterAuthCompanyData(array $companyData, $authUser): array {
        if (!isset($companyData['company_app_authentication']) || empty($companyData['company_app_authentication'])) {
            $errorMessage = 'No configuration authentication company in response control center service';

            Log::warning($errorMessage, [
                'auth_user_id' => $authUser->auth_user_id,
                'company_id' => $authUser->auth_user_company_id,
            ]);

            return [
                'valid' => false,
                'error_code' => AppAuthResponseCode::CompanyConfigAuthNotFound,
                'message' => $errorMessage,
            ];
        }

        $authCompanyData = $companyData['company_app_authentication'];
        //* Check domain company
        if (!$authCompanyData['company_app_authentication_domain']) {
            $errorMessage = 'Company configuration domain missing';

            Log::warning($errorMessage, [
                'auth_user_id' => $authUser->auth_user_id,
                'company_id' => $authUser->auth_user_company_id,
                'authentication_company_id' => $authCompanyData['company_app_authentication_company_id'] ?? null,
                'authentication_company_data' => $authCompanyData,
            ]);

            return [
                'valid' => false,
                'error_code' => AppAuthResponseCode::CompanyConfigAuthMissing,
                'message' => $errorMessage,
            ];
        }

        //* Check config database
        $invalidConfigValues = [null, ''];
        $invalidConfigDbHost = in_array($authCompanyData['company_app_authentication_db_host'], $invalidConfigValues);
        $invalidConfigDbPort = in_array($authCompanyData['company_app_authentication_db_port'], $invalidConfigValues);
        $invalidConfigDbDatabase = in_array($authCompanyData['company_app_authentication_db_database'], $invalidConfigValues);
        $invalidConfigDbSchema = in_array($authCompanyData['company_app_authentication_db_schema'], $invalidConfigValues);
        $invalidConfigDbUsername = in_array($authCompanyData['company_app_authentication_db_username'], $invalidConfigValues);
        $invalidConfigDbPassword = in_array($authCompanyData['company_app_authentication_db_password'], $invalidConfigValues);
        if ($invalidConfigDbHost || $invalidConfigDbPort || $invalidConfigDbDatabase || $invalidConfigDbSchema || $invalidConfigDbUsername || $invalidConfigDbPassword) {
            $errorMessage = 'Company configuration database missing';

            Log::warning($errorMessage, [
                'auth_user_id' => $authUser->auth_user_id,
                'company_id' => $authUser->auth_user_company_id,
                'authentication_company_id' => $authCompanyData['company_app_authentication_company_id'] ?? null,
                'authentication_company_data' => $authCompanyData,
            ]);

            return [
                'valid' => false,
                'error_code' => AppAuthResponseCode::CompanyConfigAuthMissing,
                'message' => $errorMessage,
            ];
        }

        $invalidConfigRedisHost = in_array($authCompanyData['company_app_authentication_redis_host'], $invalidConfigValues);
        $invalidConfigRedisPort = in_array($authCompanyData['company_app_authentication_redis_port'], $invalidConfigValues);
        $invalidConfigRedisDatabase = in_array($authCompanyData['company_app_authentication_redis_database'], $invalidConfigValues);
        $invalidConfigRedisSchema = in_array($authCompanyData['company_app_authentication_redis_schema'], $invalidConfigValues);
        $invalidConfigRedisUsername = in_array($authCompanyData['company_app_authentication_redis_username'], $invalidConfigValues);
        $invalidConfigRedisPassword = in_array($authCompanyData['company_app_authentication_redis_password'], $invalidConfigValues);
        if ($invalidConfigRedisHost || $invalidConfigRedisPort || $invalidConfigRedisDatabase || $invalidConfigRedisSchema || $invalidConfigRedisUsername || $invalidConfigRedisPassword) {
            $errorMessage = 'Company configuration redis cache missing';

            Log::warning($errorMessage, [
                'auth_user_id' => $authUser->auth_user_id,
                'company_id' => $authUser->auth_user_company_id,
                'authentication_company_id' => $authCompanyData['company_app_authentication_company_id'] ?? null,
                'authentication_company_data' => $authCompanyData,
            ]);

            return [
                'valid' => false,
                'error_code' => AppAuthResponseCode::CompanyConfigAuthMissing,
                'message' => $errorMessage,
            ];
        }

        return [
            'valid' => true,
            'message' => 'Response authentication company valid',
        ];
    }
}

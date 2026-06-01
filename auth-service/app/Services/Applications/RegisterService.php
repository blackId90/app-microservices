<?php

namespace App\Services\Applications;

use App\Enums\AppAuthResponseCode;
use App\Exceptions\{AppAuthException, ValidationFormRequestException};
use App\Jobs\{SendCompanyVerificationEmailJob, SendUserVerificationEmailJob};
use App\Repositories\Interfaces\AuthUserRepositoryInterface;
use App\Services\Clients\ControlCenterServiceClient;
use App\Services\UserCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class RegisterService {
    // use AppAuthTempSignedRoute;

    public function __construct(
        protected AuthUserRepositoryInterface $userRepo,
        protected ControlCenterServiceClient $controlCenterService,
        protected AuthRoleService $authRoleService,
        protected UserCacheService $userCache,
        protected string $localeMessage = 'en',
    ) {
    }

    /**
     * Register new user with company
     *
     * Flow:
     * 1. Get role by slug (default: admin) if role_id not provided
     * 2. Create auth user in Auth Service
     * 3. Create company + user in Control Center Service
     * 4. Validate response from Control Center Service
     * 5. Update auth_user_company_id in Auth Service
     * 6. Dispatch welcome email job to Redis queue
     *
     * @param array $authUserData Auth User data
     * @param array $companyData Company data
     * @param array $userData Profile user data
     * @return array
     */
    public function register(array $authUserData, array $companyData, array $userData): array {
        /*
        //* Check if auth user email already exists
        if ($this->userRepo->findByEmail($authUserData['auth_user_email']))
            throw new AppAuthException(AppAuthResponseCode::EmailAlreadyExists);

        //* Check if auth user username already exists
        if ($this->userRepo->findByUsername($authUserData['auth_user_username']))
            throw new AppAuthException(AppAuthResponseCode::UsernameAlreadyExists);
        */

        try {
            //* Step 1: Get role - use provided role_id or get default "admin" role
            $creator = Auth::user();
            if (!$creator || !$creator->auth_user_is_admin) {
                $role = $this->authRoleService->getAuthRoleBySlug('admin');
                if (!$role)
                    throw new AppAuthException(codeName: AppAuthResponseCode::RoleNotFound);

                $authUserData['auth_user_role_id'] = $role->auth_role_id;
            }

            //* Step 2: Create auth user in Auth Service
            $authUser = DB::transaction(function () use ($authUserData) {
                return $this->userRepo->create($authUserData);
            });
            $authUserId = $authUser->getKey();

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

    public function verifyEmail(Request $request, string $type, string $id, string $hash): void {
        //* Manual Check valid signature and expires
        // AppAuthTempSignedRoute::isValidSignature($request);

        switch ($type) {
            case 'company':
                $controlCenterResponseVerifyEmailCompany = $this->controlCenterService->verifyEmailCompany($id, $hash);

                $this->validateControlCenterResponseVerifyEmailCompany($controlCenterResponseVerifyEmailCompany);
                break;

            default:
                try {
                    $user = $this->userRepo->findById($id);

                    //* Validation hash email / $validHash = hash_hmac('sha256', $user->getKeyEmail(), config('app.key'));
                    if (!hash_equals($hash, sha1($user->getKeyEmail())))
                        throw new AppAuthException(AppAuthResponseCode::LinkVerificationInvalid);

                    if ($user->hasVerifiedEmail())
                        throw new AppAuthException(AppAuthResponseCode::EmailAlreadyVerified);

                    $user->markEmailAsVerified();
                } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                    throw new AppAuthException(AppAuthResponseCode::NotFound);
                } catch (\Exception $e) {
                    throw $e;
                }
                break;
        }
    }

    /**
     * Validate response from Control Center Service
     */
    private function validateControlCenterResponse(?array $response): void {
        if (!$response)
            throw new AppAuthException(AppAuthResponseCode::ServiceUnavailable);

        //* Check if fallback response
        if (isset($response['fallback']) && $response['fallback'] === true)
            throw new AppAuthException(AppAuthResponseCode::ServiceUnavailable);

        //* Check if client error $statusCode >= 400 && $statusCode < 500)
        if (!isset($response['status']) || ($response['status'] >= 400 && $response['status'] < 500))
            throw new ValidationFormRequestException(context: $response['errors']);

        //* Check response structure
        if (!isset($response['status']) || $response['status'] !== 201)
            throw new AppAuthException(AppAuthResponseCode::InvalidServiceResponse);

        //* Check required data
        $controlCenterCompanyId = $response['data']['company']['company_id'] ?? null;
        $controlCenterUserId = $response['data']['user']['user_id'] ?? null;
        if (!$controlCenterCompanyId || !$controlCenterUserId)
            throw new AppAuthException(AppAuthResponseCode::InvalidServiceResponse);
    }

    /**
     * Validate response from Control Center Service
     */
    private function validateControlCenterResponseVerifyEmailCompany(?array $response): void {
        if (!$response)
            throw new AppAuthException(AppAuthResponseCode::ServiceUnavailable);

        //* Check if fallback response
        if ((isset($response['fallback']) && $response['fallback'] === true) || !isset($response['status']))
            throw new AppAuthException(AppAuthResponseCode::ServiceUnavailable);

        //* Check if client error $statusCode >= 400 && $statusCode < 500)
        if (($response['status'] >= 400 && $response['status'] < 500) && (isset($response['error']) || isset($response['error']['code_name'])))
            throw new AppAuthException(codeName: AppAuthResponseCode::resolve($response['error']['code_name']));

        //* Check response structure
        if (!isset($response['status']) || $response['status'] !== 200)
            throw new AppAuthException(AppAuthResponseCode::InvalidServiceResponse);

        //* Check required data
        if (AppAuthResponseCode::resolve($response['message']) !== AppAuthResponseCode::EmailVerificationSuccess)
            throw new AppAuthException(AppAuthResponseCode::InvalidServiceResponse);
    }
}

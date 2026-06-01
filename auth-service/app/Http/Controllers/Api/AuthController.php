<?php

namespace App\Http\Controllers\Api;

use App\Enums\AppAuthResponseCode;
use App\Http\Controllers\RestController;
use App\Http\Requests\{AuthCheckAccessPermissionRequest, AuthLoginRequest, AuthRegisterRequest};
use App\Http\Resources\{AuthResource, UserPermissionResource};
use App\Services\Applications\{AuthService, LoginAttemptService, RegisterService};
use Illuminate\Http\{JsonResponse, Request};

class AuthController extends RestController {

    public function __construct(
        protected AuthService $authService,
        protected LoginAttemptService $loginAttemptService,
        protected RegisterService $registerService
    ) {}

    /**
     * Register new user with company
     *
     * @param AuthRegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(AuthRegisterRequest $request): JsonResponse {
        $validated = $request->validated();

        $authUserData = $validated['auth_user'];
        $companyData = $validated['company'];
        $userData = $validated['user'];

        $result = $this->registerService->register($authUserData, $companyData, $userData);

        return $this->formatResponse(
            message: AppAuthResponseCode::RegisterSuccess->getMessage('success'),
            data: [
                'auth_user' => new AuthResource($result['auth_user']),
                'company' => $result['company'],
                'user' => $result['user']
            ]
        );
    }

    public function verify(Request $request, string $type, string $id, string $hash): JsonResponse {
        $this->registerService->verifyEmail($request, $type, $id, $hash);

        return $this->formatResponse(
            message: AppAuthResponseCode::EmailVerificationSuccess->getMessage('success')
        );
    }

    public function login(AuthLoginRequest $request) {
        $credentials = [
            'auth_user_email' => $request->input('email'),
            'password' => $request->input('password')
        ];

        $result = $this->authService->login($credentials);
        $data = [
            'type_token' => 'bearer',
            'access_token' => $result['token'],
            'expires_in' => $result['expires_in']
        ];

        return $this->formatResponse(
            message: AppAuthResponseCode::LoginSuccess->getMessage('success'),
            data: $data
        );
    }

    public function profile() {
        $profile = $this->authService->signinProfile();
        $authUser = $profile['services']['auth_user'];

        //* Push/Inject data ke dalam relations model
        $authUser->setRelation('role', $profile['services']['role']);
        $authUser->setRelation('profile_user', (object) $profile['services']['profile_user']);
        $authUser->setRelation('company', $profile['services']['company']);

        $data = (new AuthResource($authUser))->additional([
            'cached'  => $profile['isCache'],
            'session' => $profile['session'],
        ]);

        return $this->formatResponse(
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: $data
        );
    }

    /**
     * Retrieve the user login menu
     *
     * @return void
     */
    public function menus() {
        $menus = $this->authService->signinMenus();

        return $this->formatResponse(
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: UserPermissionResource::collection($menus)
        );
    }

    /**
     * Check has permission access
     *
     * @param AuthCheckAccessPermissionRequest $request
     * @return void
     */
    public function checkAccessPermission(AuthCheckAccessPermissionRequest $request) {
        $result = $this->authService->hasAccessPermission($request);

        return $this->formatResponse(
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: $result
        );
    }

    public function logout() {
        $this->authService->logout();

        return $this->formatResponse(
            message: AppAuthResponseCode::LogoutSuccess->getMessage('success'),
            data: null
        );
    }
}

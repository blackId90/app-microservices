<?php

namespace App\Http\Controllers\Api;

use App\Enums\AppAuthResponseCode;
use App\Http\Controllers\RestController;
use App\Http\Requests\InternalDestroyRegisterRequest;
use App\Http\Requests\InternalRegisterRequest;
use App\Http\Resources\InternalCompanyResource;
use App\Http\Resources\UserResource;
use App\Services\Applications\InternalRegisterService;
use Illuminate\Http\JsonResponse;

class InternalRegisterController extends RestController {

    public function __construct(
        protected InternalRegisterService $internalRegisterService
    ) {
    }

    /**
     * Internal register - create company and user
     * Called from Auth Service during user registration
     *
     * @param InternalRegisterRequest $request
     * @return JsonResponse
     */
    public function register(InternalRegisterRequest $request): JsonResponse {
        $validated = $request->validated();

        $companyData = $validated['company'];
        $userData = $validated['user'];

        //* Execute register process
        $result = $this->internalRegisterService->register($companyData, $userData);

        return $this->formatResponse(
            status: 201,
            message: AppAuthResponseCode::SuccessCreate->value,
            data: [
                'company' => new InternalCompanyResource($result['company']),
                'user' => new UserResource($result['user']),
            ]
        );
    }

    public function verify(string $companyId, string $hash): JsonResponse {
        $this->internalRegisterService->verifyCompanyEmail($companyId, $hash);

        return $this->formatResponse(
            message: AppAuthResponseCode::EmailVerificationSuccess->value
        );
    }

    /**
     * Internal destroy register - delete company and user
     * Called from Auth Service during user/company deprovisioning
     *
     * @param InternalDestroyRegisterRequest $request
     * @param string $userAuthUserId
     * @return JsonResponse
     */
    public function destroyRegister(InternalDestroyRegisterRequest $request, string $companyId, string $userAuthUserId): JsonResponse {
        $result = $this->internalRegisterService->destroyRegister($companyId, $userAuthUserId);

        return $this->formatResponse(
            status: 200,
            message: AppAuthResponseCode::SuccessDelete->value,
            data: $result
        );
    }
}


<?php

namespace App\Http\Controllers\Api;

use App\Enums\AppAuthResponseCode;
use App\Http\Controllers\RestController;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\UserResource;
use App\Services\Applications\AuthUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends RestController {

    public function __construct(
        protected AuthUserService $authUserService
    ) {}

    /**
     * Display a listing of the resource.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuthProfile(Request $request): JsonResponse {
        //* Get User Data
        // DB::enableQueryLog();
        $signinData = $this->authUserService->signinProfile($request);
        // dd(DB::getQueryLog());

        return $this->formatResponse(
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: [
                // 'isCacheUser' => $signinData['isCacheUser'],
                'user' => $signinData['user'] ? new UserResource($signinData['user']) : null, // UserResource::collection([$userData]),
                // 'isCacheCompany' => $signinData['isCacheCompany'],
                'company' => $signinData['company'] ? new CompanyResource($signinData['company']) : null, // CompanyResource::collection([$companyData]),
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyAuthProfile(Request $request): JsonResponse {
        $this->authUserService->destroySigninProfile($request);

        return $this->formatResponse(
            message: AppAuthResponseCode::LogoutSuccess->getMessage('success'),
            data: null
        );
    }
}

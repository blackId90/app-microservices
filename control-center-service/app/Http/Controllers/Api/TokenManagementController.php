<?php

namespace App\Http\Controllers\Api;

use App\Enums\AppAuthResponseCode;
use App\Http\Controllers\RestController;
use App\Http\Requests\TokenManagementListRequest;
use App\Http\Requests\TokenManagementRequest;
use App\Services\Applications\TokenManagementService;
use Illuminate\Http\JsonResponse;

class TokenManagementController extends RestController {

    public function __construct(
        protected TokenManagementService $tokenManagementService,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(TokenManagementListRequest $request): JsonResponse {
        //* Get control center data (remote control center service)
        $authData = $this->tokenManagementService->listManagementToken();

        return $this->formatResponse(
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: $authData
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TokenManagementRequest $request): JsonResponse {
        $userId = $request->route('authUserId');

        $authData = $this->tokenManagementService->storeManagementToken($userId);

        return $this->formatResponse(
            message: AppAuthResponseCode::BannedTokenSuccess->getMessage('success'),
            data: $authData
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(string $id) {
    public function destroy(TokenManagementRequest $request, string $id): JsonResponse {
        $authData = $this->tokenManagementService->destroyManagementToken($id);

        return $this->formatResponse(
            message: AppAuthResponseCode::UnbannedTokenSuccess->getMessage('success'),
            data: $authData
        );
    }
}

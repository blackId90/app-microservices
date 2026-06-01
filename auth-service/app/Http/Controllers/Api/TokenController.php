<?php

namespace App\Http\Controllers\Api;

use App\Enums\AppAuthResponseCode;
use App\Http\Controllers\RestController;
use App\Http\Requests\TokenListRequest;
use App\Http\Requests\TokenRequest;
use App\Services\Applications\AuthService;
use App\Services\JwtRedisService;
use Illuminate\Http\JsonResponse;

class TokenController extends RestController {

    public function __construct(
        protected JwtRedisService $jwtRedis,
        protected AuthService $authService
    ) {}

    /**
     * Get all banned tokens (Admin only)
     */
    public function getBannedTokens(TokenListRequest $request): JsonResponse {
        $bannedTokens = $this->jwtRedis->getAllBannedTokens();

        $data = [
            'total' => count($bannedTokens),
            'tokens' => $bannedTokens
        ];

        return $this->formatResponse(
            message: AppAuthResponseCode::SuccessRetrieveData->value, // AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: $data
        );
    }

    /**
     * Ban a token (different from logout)
     */
    public function banToken(TokenRequest $request): JsonResponse {
        $userId = $request->route('authUserId');

        $this->authService->bannedToken($userId);

        $data = [
            'user_id' => $userId
        ];

        return $this->formatResponse(
            message: AppAuthResponseCode::BannedTokenSuccess->value, // AppAuthResponseCode::BannedTokenSuccess->getMessage('success'),
            data: $data
        );
    }

    /**
     * Unban a token
     */
    public function unbanToken(TokenRequest $request): JsonResponse {
        $data = [
            'user_id' => $request->user_id
        ];

        $this->authService->unbannedToken($request->user_id);

        return $this->formatResponse(
            message: AppAuthResponseCode::UnbannedTokenSuccess->value, // AppAuthResponseCode::UnbannedTokenSuccess->getMessage('success'),
            data: $data
        );
    }
}

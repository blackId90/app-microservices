<?php

namespace App\Services\Applications;

use App\Enums\AppAuthResponseCode;
use App\Exceptions\AppControlCenterException;
use App\Services\Clients\TokenManagementClient;

class TokenManagementService {

    /**
     * Create a new class instance.
     */
    public function __construct(
        protected TokenManagementClient $tokenManagementClient,
    ) {}

    public function listManagementToken(): array {
        //* Step 1: Communication Internal Service Token Management in Auth Service
        $authManagementTokenResponse = $this->tokenManagementClient->getListTokenManagement();

        //* Step 2: Validate Response Auth Token Management
        $this->validateAuthManagementTokenResponse($authManagementTokenResponse);

        return $authManagementTokenResponse['data'];
    }

    public function storeManagementToken(string $authUserId): array {
        //* Step 1: Communication Internal Service Token Management in Auth Service
        $authManagementTokenResponse = $this->tokenManagementClient->storeBannedTokenManagement($authUserId);

        //* Step 2: Validate Response Auth Token Management
        $this->validateAuthManagementTokenResponse($authManagementTokenResponse);

        return $authManagementTokenResponse['data'];
    }

    public function destroyManagementToken(string $authUserId): array {
        //* Step 1: Communication Internal Service Token Management in Auth Service
        $authManagementTokenResponse = $this->tokenManagementClient->destroyUnbannedTokenManagement($authUserId);

        //* Step 2: Validate Response Auth Token Management
        $this->validateAuthManagementTokenResponse($authManagementTokenResponse);

        return $authManagementTokenResponse['data'];
    }

    /**
     * Validate response from Auth Service
     */
    private function validateAuthManagementTokenResponse(?array $response): void {
        if (!$response)
            throw new AppControlCenterException(AppAuthResponseCode::ServiceUnavailable);

        //* Check if fallback response
        if ((isset($response['fallback']) && $response['fallback'] === true) || !isset($response['status']))
            throw new AppControlCenterException(AppAuthResponseCode::ServiceUnavailable);

        //* Check if client error $statusCode >= 400 && $statusCode < 500)
        if (($response['status'] >= 400 && $response['status'] < 500) && (isset($response['error']) || isset($response['error']['code_name'])))
            throw new AppControlCenterException(codeName: AppAuthResponseCode::resolve($response['error']['code_name']));

        //* Check response structure
        if (!isset($response['status']) || $response['status'] !== 200)
            throw new AppControlCenterException(AppAuthResponseCode::InvalidServiceResponse);

        //* Check required data
        $validEnumValue = [
            AppAuthResponseCode::SuccessRetrieveData->value,
            AppAuthResponseCode::BannedTokenSuccess->value,
            AppAuthResponseCode::UnbannedTokenSuccess->value
        ];
        // if (AppAuthResponseCode::resolve($response['message']) !== AppAuthResponseCode::SuccessRetrieveData)
        if (!in_array($response['message'], $validEnumValue, true))
            throw new AppControlCenterException(AppAuthResponseCode::InvalidServiceResponse);
    }
}

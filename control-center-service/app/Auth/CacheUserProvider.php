<?php

namespace App\Auth;

// use App\Models\User;
use App\Services\UserCacheService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class CacheUserProvider implements UserProvider {
    public function __construct(
        protected UserCacheService $userCacheService
    ) {
    }

    /**
     * Retrieve a user by their unique identifier.
     */
    public function retrieveById($identifier): ?Authenticatable {
        return $this->userCacheService->getUser($identifier);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     */
    public function retrieveByToken($identifier, $token): ?Authenticatable {
        //* Not needed for JWT authentication
        return null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     */
    public function updateRememberToken(Authenticatable $user, $token): void {
        //* Not needed for JWT authentication
    }

    /**
     * Retrieve a user by the given credentials.
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable {
        if (isset($credentials['user_auth_user_id']))
            return $this->userCacheService->getUser($credentials['user_auth_user_id']);

        if (isset($credentials['user_id']))
            return $this->userCacheService->getUserById($credentials['user_id']);

        return null;
    }

    /**
     * Validate a user against the given credentials.
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool {
        //* JWT validation is handled by JwtGuard, not by password comparison
        return true;
    }

    /**
     * Rehash the user's password if required and supported.
     */
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void {
        //* Not needed for JWT authentication
    }
}

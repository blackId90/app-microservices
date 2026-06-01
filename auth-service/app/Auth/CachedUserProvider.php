<?php

namespace App\Auth;

use App\Models\AuthUser;
use App\Services\UserCacheService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;

class CachedUserProvider implements UserProvider {

    /**
     * Create a new class instance.
     */
    public function __construct(
        protected UserCacheService $userCache
    ) {}

    /**
     * Retrieve a user by their unique identifier (UUID / primary key).
     */
    public function retrieveById($identifier): ?Authenticatable {
        return $this->userCache->getUser((string) $identifier);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     * JWT tidak pakai remember token — always return null.
     *
     * Signature TIDAK boleh ditambah type hint pada $identifier & $token
     * karena contract Laravel 12 tidak mendeklarasikan type hint di sana.
     */
    public function retrieveByToken($identifier, #[\SensitiveParameter] $token): ?Authenticatable {
        // JWT stateless, tidak ada remember_token
        return null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     * No-op karena JWT tidak membutuhkan remember token.
     *
     * Signature parameter $token tidak boleh diberi type hint string
     * agar compatible dengan contract.
     */
    public function updateRememberToken(Authenticatable $user, #[\SensitiveParameter] $token): void {
        // No-op: JWT stateless, tidak perlu remember token
    }

    /**
     * Retrieve a user by the given credentials (email / username).
     */
    public function retrieveByCredentials(#[\SensitiveParameter] array $credentials): ?Authenticatable {
        $identifier = $credentials['auth_user_email'] ?? $credentials['email'] ?? $credentials['auth_user_username'] ?? null;
        if (!$identifier)
            return null;

        $user = $this->userCache->getUserByEmail($identifier);
        if (!$user)
            $user = $this->userCache->getUserByUsername($identifier);

        return $user;
    }

    /**
     * Validate a user's credentials (password check).
     */
    public function validateCredentials(Authenticatable $user, #[\SensitiveParameter] array $credentials): bool {
        $plain = $credentials['password'] ?? null;
        if (!$plain)
            return false;

        /** @var AuthUser $user */
        return Hash::check($plain, $user->getAuthPassword());
    }

    /**
     * Rehash the user's password if required (Laravel 12+).
     */
    public function rehashPasswordIfRequired(Authenticatable $user, #[\SensitiveParameter] array $credentials, bool $force = false): void {
        // No-op: password rehash ditangani di AuthService saat login
    }
}

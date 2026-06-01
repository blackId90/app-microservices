<?php

namespace App\Auth;

use App\Enums\AppAuthResponseCode;
use App\Exceptions\JWTAuthException;
use App\Exceptions\TokenBlacklistedException;
use App\Services\JwtRedisService;
use App\Services\JwtTokenService;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Log;

class JwtGuard implements Guard {
    use GuardHelpers;

    protected ?string $token = null;
    protected ?array $payload = null;

    public function __construct(
        UserProvider $provider,
        protected Request $request,
        protected JwtTokenService $jwtTokenService,
        protected JwtRedisService $jwtRedisService
    ) {
        $this->provider = $provider;
    }

    /**
     * Get the currently authenticated user.
     */
    public function user() {
        //* Return if already retrieved
        if ($this->user !== null)
            return $this->user;

        //* Get token from request
        $token = $this->getTokenFromRequest();
        if (!$token)
            return null;

        try {
            //* Decode and validate JWT
            $payload = $this->jwtTokenService->decode($token);
            $this->payload = $payload;

            //* Extract token ID and user ID from token
            $tokenId = $payload['jti'] ?? null;
            $userId = $payload['sub'] ?? null;
            if (!$tokenId || !$userId)
                return null;

            //* Check if token is banned
            $this->verifyBannedToken($tokenId);

            //* Verify token matches user's active session (Single Sign-In)
            $this->verifySingleSignIn($payload, $userId, $tokenId);

            //* Get token expiry for cache
            // $tokenExp = $payload['exp'] ?? null;

            //* Retrieve user from provider (which uses cache)
            $user = $this->provider->retrieveById($userId);
            if ($user) {
                $this->user = $user;
                $this->token = $token;

                //* Store additional token info in request attributes
                $this->storeTokenAttributes($payload);
            }

            return $user;
        } catch (TokenBlacklistedException $e) {
            throw $e;
        } catch (JWTAuthException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new JWTAuthException(AppAuthResponseCode::InvalidToken);
        }
    }

    /**
     * Validate a user's credentials.
     */
    public function validate(array $credentials = []): bool {
        if (!$this->provider)
            return false;

        $user = $this->provider->retrieveByCredentials($credentials);

        return $user !== null;
    }

    /**
     * Get the token from the request.
     */
    protected function getTokenFromRequest(): ?string {
        return $this->request->bearerToken();
    }

    /**
     * Check if token is banned
     */
    protected function verifyBannedToken(string $tokenId): void {
        $bannedCheck = $this->jwtRedisService->isTokenBanned($tokenId);
        if (!$bannedCheck['isValid']) {
            $reason = AppAuthResponseCode::resolve($bannedCheck['reason']);

            throw new TokenBlacklistedException($reason ?? AppAuthResponseCode::BannedToken);
        }
    }

    /**
     * Verify token matches user's active session (Single Sign-In)
     */
    protected function verifySingleSignIn(array $payload, string $userId, string $tokenId): void {
        if ($payload['temp'] || ($payload['scope'] === 'control_center_auth') || ($payload['purpose'] === 'validate_company'))
            return;

        $isValidSession = $this->jwtRedisService->verifyUserToken($userId, $tokenId);
        if (!$isValidSession)
            throw new TokenBlacklistedException(AppAuthResponseCode::TokenReplace);
    }

    /**
     * Store token attributes in request for later use.
     */
    protected function storeTokenAttributes(array $payload): void {
        $this->request->attributes->set('userId', $payload['sub'] ?? null);
        $this->request->attributes->set('roleId', $payload['role'] ?? null);
        $this->request->attributes->set('companyId', $payload['company'] ?? null);
        $this->request->attributes->set('tokenId', $payload['jti'] ?? null);
        $this->request->attributes->set('tokenScope', $payload['scope'] ?? false);
        $this->request->attributes->set('tokenPurpose', $payload['purpose'] ?? null);
        $this->request->attributes->set('tokenExpired', $payload['exp'] ?? null);
        $this->request->attributes->set('isAdmin', $payload['is_admin'] ?? false);
    }

    /**
     * Get the decoded JWT payload.
     */
    public function payload(): ?array {
        return $this->payload;
    }

    /**
     * Get the token.
     */
    public function token(): ?string {
        return $this->token;
    }

    /**
     * Set the current request instance.
     */
    public function setRequest(Request $request): self {
        $this->request = $request;

        return $this;
    }

    /**
     * Determine if the guard has a user instance.
     */
    public function hasUser(): bool {
        return $this->user !== null;
    }

    /**
     * Get the ID for the currently authenticated user.
     */
    public function id() {
        if ($this->user)
            return $this->user->getAuthIdentifier();

        return null;
    }

    /**
     * Determine if the current user is authenticated.
     */
    public function check(): bool {
        return $this->user() !== null;
    }

    /**
     * Determine if the current user is a guest.
     */
    public function guest(): bool {
        return !$this->check();
    }
}

<?php

namespace App\Auth;

use App\Enums\AppAuthResponseCode;
use App\Exceptions\TokenBlacklistedException;
use App\Exceptions\JWTAuthException;
use App\Services\JwtRedisService;
use App\Services\UserCacheService;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT;
use Tymon\JWTAuth\Payload;

class JwtCachedGuard implements Guard {
    use GuardHelpers;

    protected ?string $token = null;

    /**
     * Create a new class instance.
     */
    public function __construct(
        UserProvider $provider,
        protected Request $request,
        protected JwtRedisService $jwtRedis,
        protected UserCacheService $userCache,
        protected JWT $jwt,
    ) {
        $this->provider = $provider;
    }

    /**
     * Get the currently authenticated user.
     * Resolve from JWT token → Redis cache → DB (via UserCacheService).
     */
    public function user(): ?Authenticatable {
        if ($this->user !== null)
            return $this->user;

        try {
            //* Get token from request header
            $token = JWTAuth::getToken();
            if (!$token)
                return null;

            //* Parse and validate token (but don't fetch user from DB yet)
            $payload = JWTAuth::setToken($token)->getPayload();

            $userId  = $payload->get('sub') ?? null;
            $tokenId = $payload->get('jti') ?? null;
            if (!$userId || !$tokenId)
                return null;

            //* Check if token is banned
            $this->verifyBannedToken($tokenId);

            //* Verify token matches user's active session (Single Sign-In)
            $this->verifySingleSignIn($payload, $userId, $tokenId);

            $exp  = $payload->get('exp') ?? null;

            //* Store request attributes
            $this->request->attributes->set('tokenId', $tokenId);
            $this->request->attributes->set('tokenScope', $payload->get('scope') ?? false);
            $this->request->attributes->set('tokenPurpose', $payload->get('purpose') ?? null);
            $this->request->attributes->set('tokenExpired', $exp);
            $this->request->attributes->set('userId', $userId);

            //* Retrieve user from provider (which uses cache)
            // $user = $this->provider->retrieveById($userId);

            //* Retrieve user from cache user service
            $user = $this->userCache->getUser($userId, $exp);
            if (!$user)
                return null;

            $this->user = $user;
            $this->token = $token->get();

            //* Store additional token info in request attributes
            $this->storeTokenAttributes($payload);

            return $this->user;
        } catch (TokenBlacklistedException $e) {
            throw $e;
        } catch (TokenInvalidException) {
            throw new JWTAuthException(AppAuthResponseCode::InvalidToken);
        } catch (TokenExpiredException) {
            throw new JWTAuthException(AppAuthResponseCode::ExpiredToken);
        } catch (JWTException) {
            throw new JWTAuthException(AppAuthResponseCode::Unauthorized);
        }
    }

    /**
     * Validate credentials — digunakan oleh Auth::validate().
     */
    public function validate(array $credentials = []): bool {
        $user = $this->provider->retrieveByCredentials($credentials);
        if (!$user)
            return false;

        return $this->provider->validateCredentials($user, $credentials);
    }

    /**
     * Set current request (opsional, untuk testing / context swap).
     */
    public function setRequest(Request $request): static {
        $this->request = $request;

        return $this;
    }

    /**
     * Get raw JWT token string from request header.
     */
    public function getTokenForRequest(): ?string {
        try {
            $token = JWTAuth::getToken();

            return $token ? (string) $token : null;
        } catch (JWTException) {
            return null;
        }
    }

    /**
     * Check if token is banned
     */
    protected function verifyBannedToken(string $tokenId): void {
        $bannedCheck = $this->jwtRedis->isTokenBanned($tokenId);
        if (!$bannedCheck['isValid']) {
            $reason = AppAuthResponseCode::resolve($bannedCheck['reason']);

            throw new TokenBlacklistedException($reason);
        }
    }

    /**
     * Verify token matches user's active session (Single Sign-In)
     */
    protected function verifySingleSignIn(Payload $payload, string $userId, string $tokenId): void {
        $routeName = $this->request->route()->getName();
        if ($routeName === 'login' || $payload->get('temp') || $payload->get('scope') === 'control_center_auth' || $payload->get('purpose') === 'validate_company')
            return;

        $isValidSession = $this->jwtRedis->verifyUserToken($userId, $tokenId);
        if (!$isValidSession)
            throw new TokenBlacklistedException(
                codeName: AppAuthResponseCode::TokenReplace,
                status: 401
            );
    }

    /**
     * Store token attributes in request for later use.
     */
    protected function storeTokenAttributes(Payload $payload): void {
        $this->request->attributes->set('roleId', $payload->get('role') ?? null);
        $this->request->attributes->set('companyId', $payload->get('company') ?? null);
        $this->request->attributes->set('isAdmin', $payload->get('is_admin') ?? false);
    }

    /**
     * Login: set user secara manual dan store token di Redis.
     * Digunakan setelah AuthService::login() berhasil generate token.
     */
    public function login(Authenticatable $user): void {
        $this->user = $user;
    }

    /**
     * Logout: hapus user dari state guard.
     */
    public function logout(): void {
        $this->user = null;
        $this->token = null;
    }

    /**
     * Get the token.
     */
    public function token(): ?string {
        return $this->token;
    }

    /**
     * Cek apakah user sudah terautentikasi (helper tambahan).
     */
    public function check(): bool {
        return $this->user() !== null;
    }

    /**
     * Cek apakah user adalah guest (kebalikan check).
     */
    public function guest(): bool {
        return !$this->check();
    }

    /**
     * Ambil ID user yang sedang terautentikasi.
     */
    public function id(): mixed {
        return $this->user()?->getAuthIdentifier();
    }

    /**
     * Ambil payload langsung dari JWT (helper tambahan di luar kontrak).
     */
    public function payload(): ?array {
        try {
            $token = JWTAuth::getToken();
            if (!$token)
                return null;

            return JWTAuth::setToken($token)->getPayload()->toArray();
        } catch (JWTException) {
            return null;
        }
    }
}

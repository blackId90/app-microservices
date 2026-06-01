<?php

namespace App\Services;

use App\Enums\AppAuthResponseCode;
use Exception;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtRedisService {
    protected $redis;
    protected $ttl; // Token TTL in minutes
    protected $prefix;
    protected $prefixJWTUser = 'jwt:user';
    protected $prefixJWTBanned = 'jwt:banned';
    protected $PrefixJWTRateLimit = 'jwt:rate_limit';

    public function __construct() {
        $this->redis = Redis::connection('jwt');
        $this->ttl = config('jwt.ttl', 60); // Default 60 minutes
        $this->prefix = config('database.redis.options.prefix', '');
    }

    /**
     * Generate Redis keys
     */
    private function getUserTokenKey(string $userId): string {
        return "{$this->prefixJWTUser}:{$userId}";
    }

    private function getBannedTokenKey(string $tokenId): string {
        return "{$this->prefixJWTBanned}:{$tokenId}";
    }

    private function getRateLimitKey(string $userId): string {
        return "{$this->PrefixJWTRateLimit}:{$userId}";
    }

    /**
     * Single Sign-In: Store user's active token
     */
    public function storeUserToken(string $userId, string $token): void {
        $key = $this->getUserTokenKey($userId);
        $tokenId = $this->getTokenId($token);

        //* Get old token if exists
        $oldStoredData = $this->redis->get($key);

        //* Ban old token if exists (force logout from other devices)
        if ($oldStoredData) {
            $oldTokenId = json_decode($oldStoredData, true)['jti'];

            $this->banToken($userId, $oldTokenId);
        }

        $dataStoreAuthUser = [
            'id' => $userId,
            'jti' => $tokenId,
            // 'bearer' => $token,
            // 'reason' => $reason,
            'timestamp' => now()->toISOString()
        ];

        //* Store new token
        $this->redis->setex($key, $this->ttl * 60, json_encode($dataStoreAuthUser));

        /*
        $payload = JWTAuth::setToken($token)->getPayload();
        $exp = $payload->get('exp');

        // Hitung TTL dalam detik
        $ttl = $exp - Carbon::now()->timestamp;

        $this->redis->set($key, json_encode($dataStoreAuthUser));
        $this->redis->expireat($key, $exp);
        */
    }

    /**
     * Get user's active token
     */
    public function getUserToken(string $userId): ?string {
        $key = $this->getUserTokenKey($userId);
        return $this->redis->get($key);
    }

    /**
     * Delete user's active token (logout)
     */
    public function deleteUserToken(string $userId): void {
        $key = $this->getUserTokenKey($userId);
        $this->redis->del($key);
    }

    /**
     * @param string $userId
     *
     * @return \App\Enums\AppAuthResponseCode|void
     */
    public function verifyAndBanToken(string $userId) {
        $keyStoredUser = $this->getUserTokenKey($userId);
        $storedUser = $this->redis->get($keyStoredUser);
        if (!$storedUser)
            return AppAuthResponseCode::BannedUserHasNoActiveToken; // throw new Exception('user_has_no_active_token', 404);

        $storedUserData = json_decode($storedUser, true);
        $tokenId = $storedUserData['jti'];

        //* token_already_banned / token_already_logout (409)
        $keyStoredBanned = $this->getBannedTokenKey($tokenId);
        $storedBanned = $this->redis->get($keyStoredBanned);
        if ($storedBanned) {
            $storeBannedData = json_decode($storedBanned, true);
            if ($tokenId === $storeBannedData['jti']) {
                $isStatus = $storeBannedData['reason'] === AppAuthResponseCode::BannedToken->value ? AppAuthResponseCode::BannedTokenAlready : AppAuthResponseCode::BannedTokenHasLogout;

                return $isStatus;
            }
        }

        //* token_banned_successfully
        $this->banToken($userId, $tokenId, AppAuthResponseCode::BannedToken);
    }

    /**
     * Ban a token (different from logout)
     */
    public function banToken(string $userId, string $tokenId, ?AppAuthResponseCode $reason = AppAuthResponseCode::TokenReplace): void {
        // $oldTokenId = $this->getTokenId($token);
        $keyBannedStored = $this->getBannedTokenKey($tokenId);
        $expiry = $this->redis->ttl($this->getUserTokenKey($userId)) ?? ($this->ttl * 60);

        $dataStoreBanned = [
            'id' => $userId,
            'jti' => $tokenId,
            // 'bearer' => $token,
            'reason' => $reason,
            'timestamp' => now()->toISOString()
        ];

        if ($reason === AppAuthResponseCode::BannedToken) {
            $bannedByUserId = request()->user()->getKey();
            $dataStoreBanned['banned_by'] = $bannedByUserId;
        }

        $this->redis->setex($keyBannedStored, $expiry, json_encode($dataStoreBanned));
    }

    /**
     * @param string $userId
     *
     * @return \App\Enums\AppAuthResponseCode|void
     */
    public function verifyAndUnbanToken(string $userId) {
        $keyStoredUser = $this->getUserTokenKey($userId);
        $storedUser = $this->redis->get($keyStoredUser);
        if (!$storedUser)
            return AppAuthResponseCode::BannedUserHasNoActiveToken;

        $storedUserData = json_decode($storedUser, true);
        $tokenId = $storedUserData['jti'] ?? null;

        $keyStoredBanned = $this->getBannedTokenKey($tokenId);
        $storedBanned = $this->redis->get($keyStoredBanned);
        if (!$storedBanned)
            return AppAuthResponseCode::UnbannedTokenNotFound;

        $storedBannedData = json_decode($storedBanned, true);

        $bannedTokenId = $storedBannedData['jti'] ?? null;
        $bannedReason = $storedBannedData['reason'] ?? null;
        if (!$bannedTokenId || $bannedReason !== AppAuthResponseCode::BannedToken->value || $bannedTokenId !== $tokenId)
            return AppAuthResponseCode::UnbannedJTIMissing;

        //* unbanned_token_success
        $this->unbanToken($bannedTokenId);
    }

    /**
     * Unban a token
     */
    public function unbanToken(string $tokenId): void {
        $key = $this->getBannedTokenKey($tokenId);
        $this->redis->del($key);
    }

    /**
     * Rate Limiter: Check if user exceeded rate limit
     * @param string $userId
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $decayMinutes Time window in minutes
     * @return array ['allowed' => bool, 'remaining' => int, 'reset_at' => int]
     */
    public function checkRateLimit(string $userId, int $maxAttempts = 60, int $decayMinutes = 1): array {
        $key = $this->getRateLimitKey($userId);
        $current = (int) $this->redis->get($key);
        if ($current === 0) {
            //* First request
            $this->redis->setex($key, $decayMinutes * 60, 1);

            return [
                'allowed' => true,
                'remaining' => $maxAttempts - 1,
                'reset_at' => now()->addMinutes($decayMinutes)->timestamp
            ];
        }

        if ($current >= $maxAttempts) {
            $ttl = $this->redis->ttl($key);

            return [
                'allowed' => false,
                'remaining' => 0,
                'reset_at' => now()->addSeconds($ttl)->timestamp,
                'retry_after' => $ttl
            ];
        }

        //* Increment counter
        $this->redis->incr($key);
        $ttl = $this->redis->ttl($key);

        return [
            'allowed' => true,
            'remaining' => $maxAttempts - ($current + 1),
            'reset_at' => now()->addSeconds($ttl)->timestamp
        ];
    }

    /**
     * Reset rate limit for user
     */
    public function resetRateLimit(string $userId): void {
        $key = $this->getRateLimitKey($userId);
        $this->redis->del($key);
    }

    public function getExpiresIn(string $token) {
        try {
            $payload = JWTAuth::setToken($token)->getPayload();

            return $payload->get('exp');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get token ID from JWT token
     */
    private function getTokenId(string $token): string {
        try {
            $payload = JWTAuth::setToken($token)->getPayload();

            return $payload->get('jti') ?? md5($token);
        } catch (\Exception $e) {
            return md5($token);
        }
    }

    /**
     * Check if token is banned
     */
    public function isTokenBanned(string $tokenId): array {
        $result = ['isValid' => false, 'reason' => null];
        $key = $this->getBannedTokenKey($tokenId);
        $isBannedExists = $this->redis->exists($key) > 0;

        switch ($isBannedExists) {
            case false:
                $result['isValid'] = true;
                break;

            default:
                $dataStoredBanned = json_decode($this->redis->get($key), true);
                $result['reason'] = $dataStoredBanned['reason'];
                break;
        }

        return $result;
    }

    /**
     * Verify if current token matches user's active token (Single Sign-In check)
     */
    public function verifyUserToken(string $userId, string $currentTokenId): bool {
        $stored = $this->getUserToken($userId);
        if (!$stored)
            return false;

        $storedTokenId = json_decode($stored, true)['jti'] ?? null;

        return $storedTokenId === $currentTokenId;
    }

    /**
     * Get all banned tokens (for admin purposes)
     */
    public function getAllBannedTokens(): array {
        $keys = $this->redis->keys("{$this->prefixJWTBanned}:*");
        $tokens = [];

        foreach ($keys as $key) {
            $tokenId = Str::afterLast($key, ':');
            $keyStoredBanned = $this->getBannedTokenKey($tokenId);
            $rawStoredBanned = $this->redis->get($keyStoredBanned);
            $ttl = $this->redis->ttl($keyStoredBanned);

            if (!$rawStoredBanned)
                continue;

            $dataStoredBanned = json_decode($rawStoredBanned, true);

            $tokens[] = [
                'token_id' => $dataStoredBanned['jti'] ?? $tokenId,
                'user_id' => $dataStoredBanned['id'],
                'expires_in' => $ttl,
                'reason' => $dataStoredBanned['reason'],
                'banned_by' => $dataStoredBanned['banned_by'] ?? null,
                'timestamp' => $dataStoredBanned['timestamp']
            ];
        }

        return $tokens;
    }

    /**
     * Get user session info
     */
    public function getUserSessionInfo(string $userId): ?array {
        $token = JWTAuth::getToken();
        if (!$token)
            return null;

        try {
            $payload = JWTAuth::setToken($token)->getPayload();

            return [
                'user_id' => $userId,
                'token_id' => $payload->get('jti'),
                'issued_at' => date('Y-m-d\TH:i:s.u\Z', $payload->get('iat')), // 'issued_at' => date('Y-m-d H:i:s', $payload->get('iat')),
                'expires_at' => date('Y-m-d\TH:i:s.u\Z', $payload->get('exp')), // 'expires_at' => date('Y-m-d H:i:s', $payload->get('exp')),
                'ttl' => $this->redis->ttl($this->getUserTokenKey($userId))
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Clear all user sessions (admin function)
     */
    public function clearAllUserSessions(): int {
        $keys = $this->redis->keys("{$this->prefixJWTUser}:*");

        return count($keys) > 0 ? $this->redis->del(...$keys) : 0;
    }
}

<?php

namespace App\Services;

use App\Models\AuthUser;
use App\Models\LoginAttempt;
use Carbon\Carbon;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;

class UserCacheService {

    public function __construct(
        protected ?Connection $redis = null,
        protected int $ttl = 3600, // default 1 hour
        protected string $prefix = 'cache:auth-service:user',
        protected string $prefixData = 'data',
        protected string $prefixIdentifier = 'identifier'
    ) {
        $this->redis = Redis::connection('jwt');
        $this->ttl = config('cache.user_ttl', 3600); // 1 hour default
    }

    /**
     * Generate cache key for user
     */
    private function getUserKey(string $userId): string {
        return "{$this->prefix}:{$this->prefixData}:{$userId}";
    }

    /**
     * Generate cache key for user by email
     */
    private function getUserByEmailKey(string $email): string {
        return "{$this->prefix}:{$this->prefixIdentifier}:email:{$email}";
    }

    /**
     * Generate cache key for user by username
     */
    private function getUserByUsernameKey(string $username): string {
        return "{$this->prefix}:{$this->prefixIdentifier}:username:{$username}";
    }

    /**
     * Get user from cache or database
     */
    public function getUser(string $userId, ?int $payloadExp = null): ?AuthUser {
        $key = $this->getUserKey($userId);

        //* Try to get from cache
        $cached = $this->redis->get($key);
        if ($cached)
            return $this->deserializeUser($cached);

        //* Get from database
        $user = AuthUser::find($userId);
        if ($user)
            $this->cacheUser($user, $payloadExp);

        return $user;
    }

    /**
     * Get user by email from cache or database
     */
    public function getUserByEmail(string $email): ?AuthUser {
        $emailKey = $this->getUserByEmailKey($email);

        //* Check if we have userId cached for this email
        $userId = $this->redis->get($emailKey);
        if ($userId)
            return $this->getUser($userId);

        //* Get from database
        $user = AuthUser::where('auth_user_email', $email)->first();
        if ($user)
            $this->cacheUser($user);

        return $user;
    }

    /**
     * Get user by username from cache or database
     */
    public function getUserByUsername(string $username): ?AuthUser {
        $usernameKey = $this->getUserByUsernameKey($username);

        //* Check if we have userId cached for this username
        $userId = $this->redis->get($usernameKey);
        if ($userId)
            return $this->getUser($userId);

        //* Get from database
        $user = AuthUser::where('auth_user_username', $username)->first();
        if ($user)
            $this->cacheUser($user);

        return $user;
    }

    private function getExpiredAtCache(?int $payloadExp = null) {
        if (!$payloadExp)
            return $this->ttl;

        //* Hitung waktu expired dari payload
        $expireAt = Carbon::createFromTimestamp($payloadExp);
        $ttl = now()->diffInSeconds($expireAt, false);

        //* Cast to integer and make sure it is not negative
        $ttl = (int) $ttl;
        if ($ttl <= 0)
            return $this->ttl;

        return $ttl;
    }

    /**
     * Cache user data
     */
    public function cacheUser(AuthUser $user, ?int $payloadExp = null): void {
        $userId = $user->getKey();
        $key = $this->getUserKey($userId);
        $ttl = $this->getExpiredAtCache($payloadExp);

        //* Cache user data
        $this->redis->setex($key, $ttl, $this->serializeUser($user));

        //* Cache email -> userId mapping
        if ($user->auth_user_email) {
            $emailKey = $this->getUserByEmailKey($user->auth_user_email);
            $this->redis->setex($emailKey, $ttl, $userId);
        }

        //* Cache username -> userId mapping
        if ($user->auth_user_username) {
            $usernameKey = $this->getUserByUsernameKey($user->auth_user_username);
            $this->redis->setex($usernameKey, $ttl, $userId);
        }
    }

    /**
     * Update user in cache and database
     */
    public function updateUser(string $userId, array $data): ?AuthUser {
        $user = AuthUser::find($userId);

        if (!$user) {
            return null;
        }

        //* If email or username is being changed, invalidate old cache
        $oldEmail = $user->auth_user_email;
        $oldUsername = $user->auth_user_username;

        //* Update database
        $user->update($data);

        //* Invalidate old email/username cache if changed
        if (isset($data['auth_user_email']) && $data['auth_user_email'] !== $oldEmail) {
            $this->invalidateUserByEmail($oldEmail);
        }

        if (isset($data['auth_user_username']) && $data['auth_user_username'] !== $oldUsername) {
            $this->invalidateUserByUsername($oldUsername);
        }

        //* Refresh user from database to get updated data
        $user->refresh();

        //* Update cache with new data
        $this->cacheUser($user);

        return $user;
    }

    /**
     * Delete user from cache (with DB query)
     */
    public function invalidateUser(string $userId): void {
        $user = AuthUser::find($userId);

        if ($user) {
            //* Delete main cache
            $key = $this->getUserKey($userId);
            $this->redis->del($key);

            //* Delete email mapping
            if ($user->auth_user_email) {
                $this->invalidateUserByEmail($user->auth_user_email);
            }

            //* Delete username mapping
            if ($user->auth_user_username) {
                $this->invalidateUserByUsername($user->auth_user_username);
            }
        }
    }

    /**
     * Delete main user cache, email mapping cache, username mapping cache from cache (without DB query)
     */
    public function invalidateUserCacheOnly(string $userId): void {
        $key = $this->getUserKey($userId);

        //* Try to get data user from cache
        $userCached = $this->redis->get($key);
        if ($userCached) {
            $storedUserData = $this->deserializeUser($userCached);

            //* Delete email mapping
            $username = $storedUserData['auth_user_username'];
            if ($username)
                $this->invalidateUserByUsername($username);

            //* Delete username mapping
            $email = $storedUserData['auth_user_email'];
            if ($email)
                $this->invalidateUserByEmail($email);

            //* Delete main cache
            $this->redis->del($key);
        }
    }

    /**
     * Delete user by email from cache
     */
    public function invalidateUserByEmail(string $email): void {
        $emailKey = $this->getUserByEmailKey($email);
        $this->redis->del($emailKey);
    }

    /**
     * Delete user by username from cache
     */
    public function invalidateUserByUsername(string $username): void {
        $usernameKey = $this->getUserByUsernameKey($username);
        $this->redis->del($usernameKey);
    }

    /**
     * Serialize user for caching
     */
    private function serializeUser(AuthUser $user): string {
        return json_encode([
            'id' => $user->getKey(),
            'attributes' => $user->attributesToArray(),
            'original' => $user->getOriginal(),
            'relations' => $user->getRelations(),
        ]);
    }

    /**
     * Deserialize user from cache
     */
    private function deserializeUser(string $data): AuthUser {
        $userData = json_decode($data, true);

        $user = new AuthUser();
        $user->exists = true;
        $user->setRawAttributes($userData['attributes']);

        //* Restore original attributes
        if (isset($userData['original'])) {
            $user->syncOriginal();
        }

        //* Restore relations if any
        if (isset($userData['relations']['latestLoginAttempt'])) {
            $loginAttempt = new LoginAttempt($userData['relations']['latestLoginAttempt']);
            $loginAttempt->exists = true;
            $user->setRelation('latestLoginAttempt', $loginAttempt);
        }
        /*
        if (isset($userData['relations'])) {
            foreach ($userData['relations'] as $relation => $value) {
                $user->setRelation($relation, $value);
            }
        }
        */

        return $user;
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array {
        $keys = $this->redis->keys("{$this->prefix}:*");

        $stats = [
            'total_cached_users' => 0,
            'email_mappings' => 0,
            'username_mappings' => 0,
        ];

        foreach ($keys as $key) {
            if (strpos($key, ':email:') !== false) {
                $stats['email_mappings']++;
            } elseif (strpos($key, ':username:') !== false) {
                $stats['username_mappings']++;
            } else {
                $stats['total_cached_users']++;
            }
        }

        return $stats;
    }

    /**
     * Clear all user cache
     */
    public function clearAllCache(): int {
        $keys = $this->redis->keys("{$this->prefix}:*");
        if (empty($keys)) {
            return 0;
        }

        return $this->redis->del(...$keys);
    }

    /**
     * Warm up cache for multiple users
     */
    public function warmUpCache(array $userIds): void {
        $users = AuthUser::whereIn('auth_user_id', $userIds)->get();

        foreach ($users as $user) {
            $this->cacheUser($user);
        }
    }

    /**
     * Get TTL for user cache
     */
    public function getCacheTTL(string $userId): ?int {
        $key = $this->getUserKey($userId);
        $ttl = $this->redis->ttl($key);

        return $ttl > 0 ? $ttl : null;
    }

    /**
     * Refresh cache TTL for user
     */
    public function refreshCacheTTL(string $userId): bool {
        $key = $this->getUserKey($userId);
        if ($this->redis->exists($key)) {
            return $this->redis->expire($key, $this->ttl);
        }

        return false;
    }

    /**
     * Check if user exists in cache
     */
    public function isCached(string $userId): bool {
        $key = $this->getUserKey($userId);
        return $this->redis->exists($key) > 0;
    }

    /**
     * Get multiple users from cache or database (batch operation)
     */
    public function getMultipleUsers(array $userIds): array {
        $users = [];
        $missingIds = [];

        //* Try to get from cache first
        foreach ($userIds as $userId) {
            $user = $this->getUser($userId);
            if ($user) {
                $users[$userId] = $user;
            } else {
                $missingIds[] = $userId;
            }
        }

        //* Get missing users from database and cache them
        if (!empty($missingIds)) {
            $dbUsers = AuthUser::whereIn('auth_user_id', $missingIds)->get();

            foreach ($dbUsers as $user) {
                $this->cacheUser($user);
                $users[$user->getKey()] = $user;
            }
        }

        return $users;
    }
}

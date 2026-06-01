<?php

namespace App\Services;

// use App\Models\AuthUser;
use App\Models\RegDistrict;
use App\Models\RegProvince;
use App\Models\RegRegency;
use App\Models\RegVillage;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;

class UserCacheService {

    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected JwtRedisService $jwtRedisService,
        protected ?Connection $redis = null,
        protected int $ttl = 3600, // default 1 hour
        protected string $prefix = 'cache:control-center-service:user',
        protected string $prefixData = 'data',
        protected string $prefixIdentifier = 'identifier'
    ) {
        $this->redis = Redis::connection('jwt');
        $this->ttl = config('cache.user_ttl', 3600); // 1 hour default
    }

    /**
     * Get user from cache or database
     */
    public function getUser(string $authUserId, ?int $payloadExp = null): ?User {
        $key = $this->getAuthUserKey($authUserId);

        //* Try to get from cache
        $cached = $this->redis->get($key);
        if ($cached)
            return $this->deserializeUser($cached);

        //* Get from database
        $user = $this->userRepository->findByAuthUserId($authUserId);
        if ($user)
            $this->cacheUser($user, $payloadExp);

        return $user;
    }

    /**
     * Get user by user_id from cache or database
     */
    public function getUserById(string $userId): ?User {
        $userKey = $this->getUserByIdKey($userId);

        //* Check if we have userId cached for this user_id
        $authUserId = $this->redis->get($userKey);
        if ($authUserId)
            return $this->getUser($authUserId);

        //* Get from database
        $user = $this->userRepository->findByUserId($userId);
        if ($user)
            $this->cacheUser($user);

        return $user;
    }

    /**
     * Cache user data
     */
    public function cacheUser(User $user, ?int $payloadExp = null): void {
        $authUserId = $user?->user_auth_user_id ?? null; // $user->getKey();
        if (!$authUserId)
            throw new \InvalidArgumentException("Auth User ID is required for caching");

        $key = $this->getAuthUserKey($authUserId);
        $ttl = $this->getExpiredAtCache($authUserId, $payloadExp);

        //* Cache user data
        $this->redis->setex($key, $ttl, $this->serializeUser($user));

        //* Cache userId -> authUserId mapping
        if ($user->user_id) {
            $userIdKey = $this->getUserByIdKey($user->user_id);
            $this->redis->setex($userIdKey, $ttl, $authUserId);
        }
    }

    /**
     * Update user in cache and database
     */
    public function updateUser(string $authUserId, array $data): ?User {
        $user = $this->userRepository->findByAuthUserId($authUserId);
        if (!$user)
            return null;

        //* Update database
        $user->update($data);

        //* Refresh user from database to get updated data
        $user->refresh();

        //* Update cache with new data
        $this->cacheUser($user);

        return $user;
    }

    /**
     * Delete user by user_auth_user_id from cache (with DB query)
     */
    public function invalidateUser(string $authUserId): void {
        $user = $this->userRepository->findByAuthUserId($authUserId);
        if ($user) {
            //* Delete main cache
            $key = $this->getAuthUserKey($authUserId);
            $this->redis->del($key);

            //* Delete email mapping
            if ($user->user_id)
                $this->invalidateUserByUserId($user->user_id);
        }
    }

    /**
     * Delete main user cache, user_id mapping cache from cache (without DB query)
     */
    public function invalidateUserCacheOnly(string $authUserId): void {
        $key = $this->getAuthUserKey($authUserId);

        //* Try to get data user from cache
        $userCached = $this->redis->get($key);
        if ($userCached) {
            $storedUserData = $this->deserializeUser($userCached);

            //* Delete username mapping
            $userId = $storedUserData['user_id'];
            if ($userId)
                $this->invalidateUserByUserId($userId);

            //* Delete main cache
            $this->redis->del($key);
        }
    }

    /**
     * Delete user by user_id from cache
     */
    public function invalidateUserByUserId(string $userId): void {
        $userIdKey = $this->getUserByIdKey($userId);
        $this->redis->del($userIdKey);
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array {
        $keys = $this->redis->keys("{$this->prefix}:*");

        $stats = [
            'total_cached_users' => 0,
            'user_id_mappings' => 0
        ];

        foreach ($keys as $key) {
            if (strpos($key, ':user_id:') !== false) {
                $stats['user_id_mappings']++;
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
        if (empty($keys))
            return 0;

        return $this->redis->del(...$keys);
    }

    /**
     * Warm up cache for multiple users
     */
    public function warmUpCache(array $userIds): void {
        $users = $this->userRepository->findUsersByKeyIds(ids: $userIds);

        foreach ($users as $user) {
            $this->cacheUser($user);
        }
    }

    /**
     * Get TTL for user cache
     */
    public function getCacheTTL(string $authUserId): ?int {
        $key = $this->getAuthUserKey($authUserId);
        $ttl = $this->redis->ttl($key);

        return $ttl > 0 ? $ttl : null;
    }

    /**
     * Refresh cache TTL for user
     */
    public function refreshCacheTTL(string $authUserId): bool {
        $key = $this->getAuthUserKey($authUserId);
        if ($this->redis->exists($key))
            return $this->redis->expire($key, $this->ttl);

        return false;
    }

    /**
     * Check if user exists in cache
     */
    public function isCached(string $authUserId): bool {
        $key = $this->getAuthUserKey($authUserId);

        return $this->redis->exists($key) > 0;
    }

    /**
     * Get multiple users from cache or database (batch operation)
     */
    public function getMultipleUsers(array $authUserIds): array {
        $users = [];
        $missingIds = [];

        //* Try to get from cache first
        foreach ($authUserIds as $authUserId) {
            $user = $this->getUser($authUserId);
            if ($user) {
                $users[$authUserId] = $user;
            } else {
                $missingIds[] = $authUserId;
            }
        }

        //* Get missing users from database and cache them
        if (!empty($missingIds)) {
            $dbUsers = $this->userRepository->findUsersByKeyIds(ids: $missingIds, key: 'user_auth_user_id');

            foreach ($dbUsers as $user) {
                $this->cacheUser($user);
                $users['user_auth_user_id'] = $user; // $users[$user->getKey()] = $user;
            }
        }

        return $users;
    }

    /**
     * Generate cache key for user by user_auth_user_id
     */
    private function getAuthUserKey(string $authUserId): string {
        return "{$this->prefix}:{$this->prefixData}:{$authUserId}";
    }

    /**
     * Generate cache key for user by user_id
     */
    private function getUserByIdKey(string $userId): string {
        return "{$this->prefix}:{$this->prefixIdentifier}:user_id:{$userId}";
    }

    private function getExpiredAtCache(string $authUserId, ?int $payloadExp = null) {
        if ($payloadExp)
            return $this->calculateExpires($payloadExp);

        //* Get Info Session Auth User Service
        $sessionInfo = $this->jwtRedisService->getUserSessionInfo($authUserId);
        if (isset($sessionInfo['expires_at']) && $sessionInfo['expires_at'])
            return $this->calculateExpires($sessionInfo['expires_at']);

        return $this->ttl;
    }

    /**
     * Calculate the expiry time of the payload
     *
     * @param integer $expiredAt
     * @return integer
     */
    private function calculateExpires(int $expiredAt): int {
        $expireAt = Carbon::createFromTimestamp($expiredAt);
        $ttl = now()->diffInSeconds($expireAt, false);

        //* Cast to integer and make sure it is not negative
        $ttl = (int) $ttl;
        if ($ttl <= 0)
            return $this->ttl;

        return $ttl;
    }

    /**
     * Serialize user for caching
     */
    private function serializeUser(User $user): string {
        return json_encode([
            'id' => $user->getKey(),
            'attributes' => $user->attributesToArray(),
            'relations' => $this->serializeRelations($user->getRelations()),
        ]);
    }

    /**
     * Serialize relations recursively
     */
    private function serializeRelations(array $relations): array {
        $result = [];

        foreach ($relations as $name => $relation) {
            if ($relation instanceof Model) {
                $result[$name] = [
                    'attributes' => $relation->attributesToArray(),
                    'relations' => $this->serializeRelations($relation->getRelations()),
                ];
            } elseif ($relation instanceof Collection) {
                $result[$name] = $relation->map(fn($model) => [
                    'attributes' => $model->attributesToArray(),
                    'relations' => $this->serializeRelations($model->getRelations()),
                ])->toArray();
            }
        }

        return $result;
    }

    /**
     * Deserialize user from cache
     */
    private function deserializeUser(string $data): User {
        $userData = json_decode($data, true);

        $user = new User();
        $user->exists = true;
        $user->setRawAttributes($userData['attributes']);

        foreach ($userData['relations'] ?? [] as $relation => $value) {
            $user->setRelation($relation, $this->deserializeRelation($relation, $value));
        }

        return $user;
    }

    /**
     * Deserialize relations recursively
     */
    private function deserializeRelation(string $relation, mixed $data): mixed {
        $modelClass = match ($relation) {
            'village'  => RegVillage::class,
            'district' => RegDistrict::class,
            'regency'  => RegRegency::class,
            'province' => RegProvince::class,
            // tambahkan mapping lain sesuai relasi User
            default    => null,
        };

        if (!$modelClass)
            return null;

        //* Single model relation
        if (isset($data['attributes'])) {
            $model = new $modelClass();
            $model->exists = true;
            $model->setRawAttributes($data['attributes']);

            foreach ($data['relations'] ?? [] as $nestedRelation => $nestedValue) {
                $model->setRelation($nestedRelation, $this->deserializeRelation($nestedRelation, $nestedValue));
            }

            return $model;
        }

        //* Collection relation
        if (is_array($data) && array_is_list($data)) {
            return collect($data)->map(function ($item) use ($modelClass) {
                $model = new $modelClass();
                $model->exists = true;
                $model->setRawAttributes($item['attributes']);

                foreach ($item['relations'] ?? [] as $nestedRelation => $nestedValue) {
                    $model->setRelation($nestedRelation, $this->deserializeRelation($nestedRelation, $nestedValue));
                }

                return $model;
            });
        }

        return null;
    }
}

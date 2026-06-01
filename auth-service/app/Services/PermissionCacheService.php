<?php

namespace App\Services;

use App\Models\AuthPermission;
use App\Models\AuthRole;
use App\Models\AuthRolePermission;
use App\Repositories\Interfaces\AuthPermissionRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;

class PermissionCacheService {

    /**
     * Create a new class instance.
     */
    public function __construct(
        protected AuthPermissionRepositoryInterface $authPermissionRepository,
        protected ?Connection $redis = null,
        protected int $ttl = 3600, // default 1 hour
        protected string $prefix = 'cache:auth-service:permission',
        protected string $prefixData = 'data',
        protected string $prefixIdentifier = 'identifier',
        protected string $prefixIds = 'ids',
        protected string $prefixStats = 'stats'
    ) {
        $this->redis = Redis::connection('jwt');
        $this->ttl = config('cache.permission_ttl', 3600); // 1 hour default
    }

    /**
     * Generate cache key for permission
     */
    private function getPermissionKey(string $permissionId): string {
        return "{$this->prefix}:{$this->prefixData}:{$permissionId}";
    }

    /**
     * Generate cache key for permission by slug
     */
    private function getPermissionBySlugKey(string $slug): string {
        return "{$this->prefix}:{$this->prefixIdentifier}:slug:{$slug}";
    }

    /**
     * Generate cache key for all permission IDs
     */
    private function getAllPermissionIdsKey(): string {
        return "{$this->prefix}:{$this->prefixIds}:all";
    }

    /**
     * Generate cache key for total count stats
     */
    private function getTotalCountKey(): string {
        return "{$this->prefix}:{$this->prefixStats}:total_count";
    }

    /**
     * Generate cache key for last updated timestamp
     */
    private function getLastUpdatedKey(): string {
        return "{$this->prefix}:{$this->prefixStats}:last_updated";
    }

    /**
     * Get all permissions from cache or database
     */
    public function getAllPermissions(?int $payloadExp = null): Collection {
        $cachedPermissions = $this->getAllPermissionsFromCache();
        if ($cachedPermissions->isNotEmpty())
            return $cachedPermissions;

        //* Get from database
        $permissions = $this->authPermissionRepository->getAllPermissionsActive();
        if ($permissions->isNotEmpty())
            $this->cachePermission($permissions, $payloadExp);

        return $permissions;
    }

    /**
     * Get permission by permission ID from cache or database
     */
    public function getPermission(string $permissionId, ?int $payloadExp = null): ?AuthPermission {
        $key = $this->getPermissionKey($permissionId);

        //* Try to get from cache
        $cached = $this->redis->get($key);
        if ($cached) {
            $permission = $this->deserializePermission($cached);

            return $permission instanceof AuthPermission ? $permission : null;
        }

        //* Get from database
        $permission = $this->authPermissionRepository->findPermissionActiveByPermissionId($permissionId);
        if ($permission)
            $this->cachePermission($permission, $payloadExp);

        return $permission;
    }

    /**
     * Get permission by slug from cache or database
     */
    public function getPermissionBySlug(string $slug, ?int $payloadExp = null): ?AuthPermission {
        $slugKey = $this->getPermissionBySlugKey($slug);

        //* Check if we have permissionId cached for this slug
        $permissionId = $this->redis->get($slugKey);
        if ($permissionId)
            return $this->getPermission($permissionId);

        //* Get from database
        $permission = $this->authPermissionRepository->findPermissionActiveBySlug($slug);
        if ($permission)
            $this->cachePermission($permission, $payloadExp);

        return $permission;
    }

    /**
     * Get all permission IDs from cache or database
     */
    public function getAllPermissionIds(): array {
        $key = $this->getAllPermissionIdsKey();

        //* Try to get from cache
        $cached = $this->redis->get($key);
        if ($cached)
            return json_decode($cached, true);

        return [];
    }

    /**
     * Cache permission data
     */
    public function cachePermission(AuthPermission|Collection $permissions, ?int $payloadExp = null): void {
        $ttl = $this->getExpiredAtCache($payloadExp);

        //* Normalization: wrap single objects into arrays
        if ($permissions instanceof AuthPermission)
            $permissions = [$permissions];

        $permissionIds = [];
        $currentPermissionIds = $this->getAllPermissionIdsFromCache();
        $permissionCount = count($permissions);
        $currentTime = now()->toISOString();

        $this->redis->pipeline(function ($pipe) use ($permissions, $ttl, $permissionCount, $currentTime, &$permissionIds, $currentPermissionIds) {
            foreach ($permissions as $permission) {
                $permissionId = $permission->getKey();
                $key = $this->getPermissionKey($permissionId);

                //* Collect permission IDs
                $permissionIds[] = $permissionId;

                //* Cache permission data
                $pipe->setex($key, $ttl, $this->serializePermission($permission));

                //* Cache slug -> permissionId mapping
                if ($permission->auth_permission_slug) {
                    $slugKey = $this->getPermissionBySlugKey($permission->auth_permission_slug);
                    $pipe->setex($slugKey, $ttl, $permissionId);
                }
            }

            //* Update permission IDs cache
            $updatedPermissionIds = array_unique(array_merge($currentPermissionIds, $permissionIds));
            $permissionIdsKey = $this->getAllPermissionIdsKey();
            $pipe->setex($permissionIdsKey, $ttl, json_encode($updatedPermissionIds));

            //* Update atau set stats total count
            $totalCountKey = $this->getTotalCountKey();
            if ($this->redis->exists($totalCountKey)) {
                //* Increment existing count (karena kita hanya cache baru)
                $pipe->incrby($totalCountKey, $permissionCount);
                $pipe->expire($totalCountKey, $ttl);
            } else {
                //* Set initial count dari semua permission yang ada di database
                $pipe->setex($totalCountKey, $ttl, $permissionCount);
            }

            //* Update last updated timestamp
            $lastUpdatedKey = $this->getLastUpdatedKey();
            $pipe->setex($lastUpdatedKey, $ttl, $currentTime);
        });
    }

    /**
     * Update permission in cache and database
     */
    public function updatePermission(string $permissionId, array $data): ?AuthPermission {
        $permission = $this->authPermissionRepository->findPermissionActiveByPermissionId($permissionId);
        if (!$permission)
            return null;

        //* If slug is being changed, invalidate old cache
        $oldSlug = $permission->auth_permission_slug;

        //* Update database
        $permission->update($data);

        //* Invalidate old slug cache if changed
        if (isset($data['auth_permission_slug']) && $data['auth_permission_slug'] !== $oldSlug)
            $this->invalidatePermissionBySlug($oldSlug);

        //* Refresh permission from database to get updated data
        $permission->refresh();

        //* Update cache with new data
        $this->cachePermission($permission);

        return $permission;
    }

    /**
     * Delete permission from cache (with DB query)
     */
    public function invalidatePermission(string $permissionId): void {
        $permission = $this->authPermissionRepository->findPermissionActiveByPermissionId($permissionId);
        if ($permission) {
            //* Delete main cache
            $key = $this->getPermissionKey($permissionId);
            $this->redis->del($key);

            //* Delete slug mapping
            if ($permission->auth_permission_slug)
                $this->invalidatePermissionBySlug($permission->auth_permission_slug);

            //* Remove from permission IDs cache
            $this->removePermissionIdFromCache($permissionId);
        }
    }

    /**
     * Delete main permission cache, slug mapping cache from cache (without DB query)
     */
    public function invalidatePermissionCacheOnly(string $permissionId): void {
        $key = $this->getPermissionKey($permissionId);

        //* Try to get data permission from cache
        $permissionCached = $this->redis->get($key);
        if ($permissionCached) {
            $storedPermissionData = $this->deserializePermission($permissionCached);

            //* Delete slug mapping
            $slug = $storedPermissionData->auth_permission_slug ?? null;
            if ($slug)
                $this->invalidatePermissionBySlug($slug);

            //* Delete main cache
            $this->redis->del($key);

            //* Remove from permission IDs cache
            $this->removePermissionIdFromCache($permissionId);
        }
    }

    /**
     * Delete permission by slug from cache
     */
    public function invalidatePermissionBySlug(string $slug): void {
        $slugKey = $this->getPermissionBySlugKey($slug);
        $this->redis->del($slugKey);
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array {
        $totalCount = $this->redis->get($this->getTotalCountKey());
        $lastUpdated = $this->redis->get($this->getLastUpdatedKey());
        $permissionIds = $this->getAllPermissionIdsFromCache();
        $actualPermissions = $this->getAllPermissionsFromCache();

        return [
            'total_count' => (int) $totalCount ?: 0,
            'cached_count' => count($permissionIds),
            'actual_cached_objects' => $actualPermissions->count(),
            'last_updated' => $lastUpdated ?: 'Never',
            'ttl' => $this->ttl,
        ];
    }

    /**
     * Get cache statistics with scan (for detailed analysis)
     */
    public function getCacheStatsWithScan(): array {
        $stats = [
            'total_cached_permissions' => 0,
            'slug_mappings' => 0,
            'ids_cache' => 0,
        ];

        $cursor = null;

        do {
            //* Scan for all permission-related keys
            [$cursor, $keys] = $this->redis->scan(
                $cursor === 0 ? null : $cursor,
                ['match' => "{$this->prefix}:*", 'count' => 100]
            );

            foreach ($keys as $key) {
                if (strpos($key, ':slug:') !== false) {
                    $stats['slug_mappings']++;
                } elseif (strpos($key, ':ids:') !== false) {
                    $stats['ids_cache']++;
                } elseif (strpos($key, ':stats:') !== false) {
                    //* Skip stats keys
                    continue;
                } else {
                    $stats['total_cached_permissions']++;
                }
            }
        } while ($cursor !== 0 && $cursor !== null);

        //* Add basic stats
        $stats['total_count'] = (int)$this->redis->get($this->getTotalCountKey()) ?: 0;
        $stats['last_updated'] = $this->redis->get($this->getLastUpdatedKey()) ?: 'Never';

        return $stats;
    }

    /**
     * Clear all permission cache with scan
     */
    public function clearAllCacheWithScan(): int {
        $deletedCount = 0;
        $cursor = null;

        do {
            //* Scan for all permission-related keys
            [$cursor, $keys] = $this->redis->scan(
                $cursor === 0 ? null : $cursor,
                ['match' => "{$this->prefix}:*", 'count' => 100]
            );

            if (!empty($keys)) {
                $deleted = $this->redis->del(...$keys);
                $deletedCount += $deleted;
            }
        } while ($cursor !== 0 && $cursor !== null);

        return $deletedCount;
    }

    /**
     * Warm up cache for multiple permissions
     */
    public function warmUpCache(array $permissionIds): void {
        $permissions = $this->authPermissionRepository->getPermissionActiveByIds($permissionIds);
        if ($permissions->isNotEmpty())
            $this->cachePermission($permissions);
    }

    /**
     * Warm up all active permissions cache
     */
    public function warmUpAllActivePermissions(): void {
        $permissions = $this->authPermissionRepository->getAllPermissionsActive();
        if ($permissions->isNotEmpty())
            $this->cachePermission($permissions);
    }

    /**
     * Get multiple permissions from cache or database (batch operation)
     */
    public function getMultiplePermissions(array $permissionIds): Collection {
        $permissions = [];
        $missingIds = [];

        //* Try to get from cache first using pipeline
        $cachedResults = $this->redis->pipeline(function ($pipe) use ($permissionIds) {
            foreach ($permissionIds as $permissionId) {
                $key = $this->getPermissionKey($permissionId);
                $pipe->get($key);
            }
        });

        foreach ($cachedResults as $index => $cached) {
            $permissionId = $permissionIds[$index];

            if ($cached) {
                $deserialized = $this->deserializePermission($cached);
                if ($deserialized instanceof AuthPermission)
                    $permissions[$permissionId] = $deserialized;
            } else {
                $missingIds[] = $permissionId;
            }
        }

        //* Get missing permissions from database and cache them
        if (!empty($missingIds)) {
            $dbPermissions = $this->authPermissionRepository->getPermissionActiveByIds($missingIds);
            if ($dbPermissions->isNotEmpty()) {
                $this->cachePermission($dbPermissions);

                foreach ($dbPermissions as $permission) {
                    $permissions[$permission->getKey()] = $permission;
                }
            }
        }

        return new Collection(array_values($permissions));
    }

    /**
     * Get TTL for permission cache
     */
    public function getCacheTTL(string $permissionId): ?int {
        $key = $this->getPermissionKey($permissionId);
        $ttl = $this->redis->ttl($key);

        return $ttl > 0 ? $ttl : null;
    }

    /**
     * Refresh cache TTL for permission
     */
    public function refreshCacheTTL(string $permissionId): bool {
        $key = $this->getPermissionKey($permissionId);
        if ($this->redis->exists($key))
            return $this->redis->expire($key, $this->ttl);

        return false;
    }

    /**
     * Check if permission exists in cache
     */
    public function isCached(string $permissionId): bool {
        $key = $this->getPermissionKey($permissionId);

        return $this->redis->exists($key) > 0;
    }

    /**
     * Check if permission by slug exists in cache
     */
    public function isSlugCached(string $slug): bool {
        $slugKey = $this->getPermissionBySlugKey($slug);

        return $this->redis->exists($slugKey) > 0;
    }

    /**
     * Get all permissions from cache using permission IDs
     */
    private function getAllPermissionsFromCache(): Collection {
        $permissionIds = $this->getAllPermissionIds();
        if (empty($permissionIds))
            return new Collection();

        $permissions = [];

        //* Use pipeline untuk batch get
        $cachedResults = $this->redis->pipeline(function ($pipe) use ($permissionIds) {
            foreach ($permissionIds as $permissionId) {
                $key = $this->getPermissionKey($permissionId);
                $pipe->get($key);
            }
        });

        foreach ($cachedResults as $index => $cached) {
            if ($cached) {
                $deserialized = $this->deserializePermission($cached);
                if ($deserialized instanceof AuthPermission)
                    $permissions[] = $deserialized;
            }
        }

        return new Collection($permissions);
    }

    /**
     * Get all permission IDs from cache only
     */
    private function getAllPermissionIdsFromCache(): array {
        $key = $this->getAllPermissionIdsKey();
        $cached = $this->redis->get($key);

        return $cached ? json_decode($cached, true) : [];
    }

    /**
     * Remove permission ID from cache
     */
    private function removePermissionIdFromCache(string $permissionId): void {
        $key = $this->getAllPermissionIdsKey();
        $currentPermissionIds = $this->getAllPermissionIdsFromCache();

        if (!empty($currentPermissionIds)) {
            $updatedPermissionIds = array_values(array_diff($currentPermissionIds, [$permissionId]));

            $this->redis->pipeline(function ($pipe) use ($key, $updatedPermissionIds) {
                $pipe->setex($key, $this->ttl, json_encode($updatedPermissionIds));

                //* Update total count stats
                $totalCountKey = $this->getTotalCountKey();
                $pipe->decr($totalCountKey);

                //* Update last updated timestamp
                $lastUpdatedKey = $this->getLastUpdatedKey();
                $pipe->setex($lastUpdatedKey, $this->ttl, now()->toISOString());
            });
        }
    }

    /**
     * Get expired at cache TTL
     */
    private function getExpiredAtCache(?int $payloadExp = null): int {
        if (!$payloadExp) {
            //* Take the end time of today (23:59:59)
            $expireAt = now()->endOfDay();

            //* Calculate the difference in seconds from now until 23:59:59
            $ttl = now()->diffInSeconds($expireAt, false);

            //* If it is past 23:59:59 (negative value), set it for the next day
            if ($ttl < 0) {
                $expireAt = now()->addDay()->endOfDay();
                $ttl = now()->diffInSeconds($expireAt, false);
            }

            $ttl = (int) $ttl;
            if ($ttl <= 0)
                return $this->ttl;

            //* Make sure the minimum TTL is 60 seconds (1 minute)
            return max($ttl, 60);
        }

        //* Calculate the expiry time of the payload
        $expireAt = Carbon::createFromTimestamp($payloadExp);
        $ttl = now()->diffInSeconds($expireAt, false);

        //* Make sure it's not negative
        return max($ttl, 0);
    }

    /**
     * Serialize permission for caching
     */
    private function serializePermission(AuthPermission $permission): string {
        return json_encode([
            'id' => $permission->getKey(),
            'attributes' => $permission->attributesToArray(),
            'relations' => $this->serializeRelations($permission->getRelations()),
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
                $result[$name] = $relation->map(function (Model $model) {
                    return [
                        'attributes' => $model->attributesToArray(),
                        'relations' => $this->serializeRelations($model->getRelations()),
                    ];
                })->toArray();
            }
        }

        return $result;
    }

    /**
     * Deserialize permission from cache
     */
    private function deserializePermission(string $data): AuthPermission|Collection {
        $permissionData = json_decode($data, true);

        $permission = new AuthPermission();
        $permission->exists = true;
        $permission->setRawAttributes($permissionData['attributes']);

        foreach ($permissionData['relations'] ?? [] as $relation => $value) {
            $permission->setRelation($relation, $this->deserializeRelation($relation, $value));
        }

        return $permission;
    }

    /**
     * Deserialize relations recursively
     */
    private function deserializeRelation(string $relation, mixed $data): mixed {
        $modelClass = match ($relation) {
            'parent' => AuthPermission::class,
            'children' => AuthPermission::class,
            'roles' => AuthRole::class,
            'role_permission' => AuthRolePermission::class,
            // tambahkan mapping lain sesuai relasi Permission
            default => null,
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

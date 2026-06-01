<?php

namespace App\Services;

use App\Models\AuthPermission;
use App\Models\AuthRole;
use App\Models\AuthRolePermission;
use App\Models\AuthUser;
use App\Repositories\Interfaces\AuthRoleRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;

class RoleCacheService {

    /**
     * Create a new class instance.
     */
    public function __construct(
        protected AuthRoleRepositoryInterface $authRoleRepository,
        protected ?Connection $redis = null,
        protected int $ttl = 3600, // default 1 hour
        protected string $prefix = 'cache:auth-service:role',
        protected string $prefixData = 'data',
        protected string $prefixIdentifier = 'identifier'
    ) {
        $this->redis = Redis::connection('jwt');
        $this->ttl = config('cache.role_ttl', 3600); // 1 hour default
    }

    /**
     * Generate cache key for role
     */
    private function getRoleKey(string $roleId): string {
        return "{$this->prefix}:{$this->prefixData}:{$roleId}";
    }

    /**
     * Generate cache key for role by slug
     */
    private function getRoleBySlugKey(string $slug): string {
        return "{$this->prefix}:{$this->prefixIdentifier}:slug:{$slug}";
    }

    /**
     * Get role from cache or database
     */
    public function getRole(string $roleId, ?int $payloadExp = null): ?AuthRole {
        $key = $this->getRoleKey($roleId);

        //* Try to get from cache
        $cached = $this->redis->get($key);
        if ($cached)
            return $this->deserializeRole($cached);

        //* Get from database
        $role = $this->authRoleRepository->findByRoleId($roleId);
        if ($role)
            $this->cacheRole($role, $payloadExp);

        return $role;
    }

    /**
     * Get role by slug from cache or database
     */
    public function getRoleBySlug(string $slug, ?int $payloadExp = null): ?AuthRole {
        $slugKey = $this->getRoleBySlugKey($slug);

        //* Check if we have roleId cached for this slug
        $roleId = $this->redis->get($slugKey);
        if ($roleId)
            return $this->getRole($roleId);

        //* Get from database
        $role = $this->authRoleRepository->findBySlug($slug);
        if ($role)
            $this->cacheRole($role, $payloadExp);

        return $role;
    }

    /**
     * Cache role data
     */
    public function cacheRole(AuthRole $role, ?int $payloadExp = null): void {
        $roleId = $role->getKey();
        $key = $this->getRoleKey($roleId);
        $ttl = $this->getExpiredAtCache($payloadExp);

        //* Cache role data
        $this->redis->setex($key, $ttl, $this->serializeRole($role));

        //* Cache slug -> roleId mapping
        if ($role->auth_role_slug) {
            $slugKey = $this->getRoleBySlugKey($role->auth_role_slug);
            $this->redis->setex($slugKey, $ttl, $roleId);
        }
    }

    /**
     * Update role in cache and database
     */
    public function updateRole(string $roleId, array $data): ?AuthRole {
        //* Get from database
        $role = $this->authRoleRepository->findByRoleId($roleId);
        if (!$role)
            return null;

        //* If slug is being changed, invalidate old cache
        $oldSlug = $role->auth_role_slug;

        //* Update database
        $role->update($data);

        //* Invalidate old slug cache if changed
        if (isset($data['auth_role_slug']) && $data['auth_role_slug'] !== $oldSlug)
            $this->invalidateRoleBySlug($oldSlug);

        //* Refresh user from database to get updated data
        $role->refresh();

        //* Update cache with new data
        $this->cacheRole($role);

        return $role;
    }

    /**
     * Delete main role cache, slug mapping cache from cache (with DB query)
     */
    public function invalidateRole(string $roleId): void {
        //* Get from database
        $role = $this->authRoleRepository->findByRoleId($roleId);
        if ($role) {
            //* Delete slug mapping
            if ($role->auth_role_slug)
                $this->invalidateRoleBySlug($role->auth_role_slug);

            //* Delete main cache
            $key = $this->getRoleKey($roleId);
            $this->redis->del($key);
        }
    }

    /**
     * Delete main role cache, slug mapping cache from cache (without DB query)
     */
    public function invalidateRoleCacheOnly(string $roleId): void {
        $key = $this->getRoleKey($roleId);

        //* Try to get data role from cache
        $roleCached = $this->redis->get($key);
        if ($roleCached) {
            $storedRoleData = $this->deserializeRole($roleCached);

            //* Delete slug mapping
            $slug = $storedRoleData['auth_role_slug'];
            if ($slug)
                $this->invalidateRoleBySlug($slug);

            //* Delete main cache
            $this->redis->del($key);
        }
    }

    /**
     * Delete role by slug from cache
     */
    public function invalidateRoleBySlug(string $slug): void {
        $slugKey = $this->getRoleBySlugKey($slug);
        $this->redis->del($slugKey);
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array {
        $keys = $this->redis->keys("{$this->prefix}:*");

        $stats = [
            'total_cached_roles' => 0,
            'slug_mappings' => 0,
        ];

        foreach ($keys as $key) {
            if (strpos($key, ':slug:') !== false) {
                $stats['slug_mappings']++;
            } else {
                $stats['total_cached_roles']++;
            }
        }

        return $stats;
    }

    /**
     * Clear all role cache
     */
    public function clearAllCache(): int {
        $keys = $this->redis->keys("{$this->prefix}:*");
        if (empty($keys))
            return 0;

        return $this->redis->del(...$keys);
    }

    /**
     * Warm up cache for multiple roles
     */
    public function warmUpCache(array $roleIds): void {
        $roles = $this->authRoleRepository->getRoleActiveByIds($roleIds);

        foreach ($roles as $role) {
            $this->cacheRole($role);
        }
    }

    /**
     * Get TTL for role cache
     */
    public function getCacheTTL(string $roleId): ?int {
        $key = $this->getRoleKey($roleId);
        $ttl = $this->redis->ttl($key);

        return $ttl > 0 ? $ttl : null;
    }

    /**
     * Refresh cache TTL for role
     */
    public function refreshCacheTTL(string $roleId): bool {
        $key = $this->getRoleKey($roleId);
        if ($this->redis->exists($key))
            return $this->redis->expire($key, $this->ttl);

        return false;
    }

    /**
     * Check if role exists in cache
     */
    public function isCached(string $roleId): bool {
        $key = $this->getRoleKey($roleId);

        return $this->redis->exists($key) > 0;
    }

    /**
     * Get multiple roles from cache or database (batch operation)
     */
    public function getMultipleRoles(array $roleIds): array {
        $roles = [];
        $missingIds = [];

        //* Try to get from cache first
        foreach ($roleIds as $roleId) {
            $role = $this->getRole($roleId);
            if ($role) {
                $roles[$roleId] = $role;
            } else {
                $missingIds[] = $roleId;
            }
        }

        //* Get missing roles from database and cache them
        if (!empty($missingIds)) {
            $dbRoles = $this->authRoleRepository->getRoleActiveByIds($missingIds);

            foreach ($dbRoles as $role) {
                $this->cacheRole($role);
                $roles[$role->getKey()] = $role;
            }
        }

        return $roles;
    }

    private function getExpiredAtCache(?int $payloadExp = null) {
        if (!$payloadExp) {
            //* Ambil waktu akhir hari ini (23:59:59)
            $expireAt = now()->endOfDay();

            //* Hitung selisih detik dari sekarang sampai jam 23:59:59
            $ttl = now()->diffInSeconds($expireAt, false);

            //* Cast to integer and make sure it is not negative
            $ttl = (int) $ttl;
            if ($ttl <= 0)
                return $this->ttl;

            return $ttl;
        }

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
     * Serialize role for caching
     */
    private function serializeRole(AuthRole $role): string {
        return json_encode([
            'id' => $role->getKey(),
            'attributes' => $role->attributesToArray(),
            'relations' => $this->serializeRelations($role->getRelations()),
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
     * Deserialize role from cache
     */
    private function deserializeRole(string $data): AuthRole {
        $roleData = json_decode($data, true);

        $role = new AuthRole();
        $role->exists = true;
        $role->setRawAttributes($roleData['attributes']);

        foreach ($roleData['relations'] ?? [] as $relation => $value) {
            $role->setRelation($relation, $this->deserializeRelation($relation, $value));
        }

        return $role;
    }

    /**
     * Deserialize relations recursively
     */
    private function deserializeRelation(string $relation, mixed $data): mixed {
        $modelClass = match ($relation) {
            'user' => AuthUser::class,
            'role_permission' => AuthRolePermission::class,
            'permission' => AuthPermission::class,
            // tambahkan mapping lain sesuai relasi Role
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

<?php
// app/Services/RolePermissionCacheService.php

namespace App\Services;

use App\Repositories\Interfaces\AuthRolePermissionRepositoryInterface;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class RolePermissionCacheService {

    /**
     * Create a new class instance.
     */
    public function __construct(
        protected AuthRolePermissionRepositoryInterface $authRolePermissionRepository,
        protected ?Connection $redis = null,
        protected int $ttl = 3600,
        protected string $prefix = 'cache:auth-service:role_permission',
        protected string $prefixData = 'data',
        protected string $prefixIdentifier = 'identifier',
        protected string $prefixStats = 'stats',
        protected string $prefixActive = 'active_cache',
        protected string $prefixCheck = 'check',
    ) {
        $this->redis = Redis::connection('jwt');
        $this->ttl = config('cache.role_permission_ttl', 3600);
    }

    /**
     * Generate cache key for role permissions
     */
    private function getRolePermissionsKey(string $roleId): string {
        return "{$this->prefix}:{$this->prefixData}:{$roleId}";
    }

    /**
     * Generate cache key for role permission by permissionId
     */
    private function getRolePermissionsByPermissionKey(string $permissionId): string {
        return "{$this->prefix}:{$this->prefixIdentifier}:{$permissionId}";
    }

    /**
     * Generate cache key for role permissions active cache
     */
    private function getPermissionActiveKey(): string {
        return "{$this->prefix}:{$this->prefixStats}:{$this->prefixActive}";
    }

    /**
     * Generate cache key for role permissions remove cache
     */
    private function getAllPermissionsCheckKey(string $roleId): string {
        return "{$this->prefix}:{$this->prefixStats}:{$this->prefixCheck}:{$roleId}:*";
    }

    /**
     * Generate cache key for role permission check cache
     */
    private function getPermissionCheckKey(string $roleId, string $permissionId): string {
        return "{$this->prefix}:{$this->prefixStats}:{$this->prefixCheck}:{$roleId}:{$permissionId}";
    }

    /**
     * Retrieve all permissions assigned to a specific role
     */
    public function getRolePermissions(string $roleId): array {
        $key = $this->getRolePermissionsKey($roleId);

        //* Try to fetch from Redis Hash
        $permissions = $this->redis->hgetall($key);
        if (!empty($permissions))
            return $this->formatHashPermissions($permissions);

        //* If not found in cache, fetch from database via repository
        $permissions = $this->authRolePermissionRepository->getAllRolePermissionsById($roleId)->toArray();

        //* Cache to Redis with dynamic TTL
        $this->cacheRolePermissions($roleId, $permissions);

        return $permissions;
    }

    /**
     * Retrieve the parameter value for a specific role-permission pair
     */
    public function getPermissionParameter(string $roleId, string $permissionId): ?int {
        $key = $this->getRolePermissionsKey($roleId);

        //* Try to fetch from Redis Hash
        $parameter = $this->redis->hget($key, $permissionId);
        if ($parameter && $parameter !== false)
            return (int) $parameter;

        return null;
    }

    /**
     * Get multiple role permissions from cache or database (batch operation)
     */
    public function getMultipleRolesPermissions(array $roleIds): array {
        $result = [];
        $missingRoleIds = [];

        //* Try to get from cache first using pipeline
        $responses = $this->redis->pipeline(function ($pipe) use ($roleIds) {
            foreach ($roleIds as $roleId) {
                $pipe->hgetall($this->getRolePermissionsKey($roleId));
            }
        });

        foreach ($responses as $index => $permissions) {
            $roleId = $roleIds[$index];

            if (!empty($permissions)) {
                $result[$roleId] = $this->formatHashPermissions($permissions);
            } else {
                $missingRoleIds[] = $roleId;
            }
        }

        //* Get missing permissions from database via repository and cache them
        if (!empty($missingRoleIds)) {
            $permissions = $this->authRolePermissionRepository->getAllRolePermissionsByIds($missingRoleIds)->toArray();
            $dbPermissions = $this->groupRolePermissionsByRoleId($permissions);

            foreach ($dbPermissions as $roleId => $permissions) {
                $this->cacheRolePermissions($roleId, $permissions);
                $result[$roleId] = $permissions;
            }
        }

        return $result;
    }

    /**
     * Update or remove a single permission for a specific role.
     */
    public function updatePermission(string $roleId, string $permissionId, ?int $parameter): bool {
        //* Update database via repository
        $updated = $this->authRolePermissionRepository->updateOrInsertPermission($roleId, $permissionId, $parameter);
        if ($updated) {
            //* Update Redis Hash with dynamic TTL
            $key = $this->getRolePermissionsKey($roleId);
            $ttl = $this->getExpiredAtCache();

            if ($parameter === null) {
                //* Remove permission
                $this->redis->hdel($key, $permissionId);
                $this->redis->srem($this->getRolePermissionsByPermissionKey($permissionId), $roleId);
            } else {
                //* Add/Update permission
                $this->redis->hset($key, $permissionId, $parameter);
                $this->redis->expire($key, $ttl);
                $this->addToPermissionIndex($permissionId, $roleId);
            }

            //* Clear quick check cache
            $this->clearPermissionCheckCache($roleId, $permissionId);

            //* Invalidate user sessions
            $this->invalidateUserSessions($roleId);
        }

        return $updated;
    }

    /**
     * Bulk update permissions for a specific role (optimized for mass updates).
     */
    public function bulkUpdatePermissions(string $roleId, array $permissions): bool {
        //* Start transaction in repository
        DB::beginTransaction();

        try {
            //* Delete existing permissions via repository
            $this->authRolePermissionRepository->deleteByRoleId($roleId);

            //* Bulk insert new permissions via repository
            if (!empty($permissions))
                $this->authRolePermissionRepository->bulkInsertPermissions($roleId, $permissions);

            DB::commit();

            //* Update Redis cache with dynamic TTL
            $this->refreshRolePermissions($roleId);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            return false;
        }
    }

    /**
     * Delete role permission by roleId from cache
     */
    public function invalidateRolePermissions(string $roleId): void {
        $key = $this->getRolePermissionsKey($roleId);

        //* Get all permission IDs from hash before deletion
        $permissionIds = $this->redis->hkeys($key);

        //* Delete main cache
        $this->redis->del($key);

        //* Remove from active set
        $activeKey = $this->getPermissionActiveKey();
        $this->redis->srem($activeKey, $roleId);

        //* Remove role from permission indexes
        foreach ($permissionIds as $permissionId) {
            $this->redis->srem($this->getRolePermissionsByPermissionKey($permissionId), $roleId);
        }

        //* Clear quick check cache
        $this->clearAllPermissionCheckCache($roleId);
    }

    /**
     * Check single permission
     */
    public function hasPermission(string $roleId, string $permissionId): bool {
        //* Try quick check cache first
        $checkKey = $this->getPermissionCheckKey($roleId, $permissionId);
        $cached = $this->redis->get($checkKey);
        if ($cached)
            return (bool) $cached;

        //* Check from Redis Hash (O(1) operation)
        $key = $this->getRolePermissionsKey($roleId);
        $exists = $this->redis->hexists($key, $permissionId);
        if ($exists) {
            //* Cache result with shorter dynamic TTL (max 5 minutes)
            $checkTtl = min($this->getExpiredAtCache(), 300);
            $this->redis->setex($checkKey, $checkTtl, 1);

            return true;
        }

        return false;
    }

    /**
     * Refresh/rebuild cache untuk role
     */
    public function refreshRolePermissions(string $roleId): void {
        //* Remove old cache
        $this->invalidateRolePermissions($roleId);

        //* Fetch new data from database via repository
        $permissions = $this->authRolePermissionRepository->getAllRolePermissionsById($roleId)->toArray();

        //* Cache to Redis with dynamic TTL
        $this->cacheRolePermissions($roleId, $permissions);

        //* Clear all quick check cache for this role
        $this->clearAllPermissionCheckCache($roleId);
    }

    /**
     * Warm up cache for multiple role permissions
     */
    public function warmUpCache(array $roleIds): int {
        $count = 0;

        foreach (array_chunk($roleIds, 50) as $chunk) {
            $permissions = $this->authRolePermissionRepository->getAllRolePermissionsByIds($chunk)->toArray();

            $permissionsByRole = $this->groupRolePermissionsByRoleId($permissions);

            foreach ($permissionsByRole as $roleId => $permissions) {
                $this->cacheRolePermissions($roleId, $permissions);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get cache statistics & monitoring
     */
    public function getCacheStats(): array {
        $activeKey = $this->getPermissionActiveKey();
        $totalRoles = $this->redis->scard($activeKey);

        $pattern = "{$this->prefix}:{$this->prefixData}:*";
        $keys = $this->redis->keys($pattern);

        $totalPermissions = 0;
        $totalMemory = 0;

        foreach ($keys as $key) {
            $totalPermissions += $this->redis->hlen($key);
            $totalMemory += $this->redis->memory('usage', $key);
        }

        return [
            'total_cached_roles' => $totalRoles,
            'total_cached_permissions' => $totalPermissions,
            'estimated_memory_bytes' => $totalMemory,
            'avg_permissions_per_role' => $totalRoles > 0 ? round($totalPermissions / $totalRoles, 2) : 0,
        ];
    }

    /**
     * Cache role permissions into a Redis hash
     */
    private function cacheRolePermissions(string $roleId, array $permissions): void {
        $key = $this->getRolePermissionsKey($roleId);
        $ttl = $this->getExpiredAtCache();

        $hashData = [];
        $permissionIds = [];

        foreach ($permissions as $permission) {
            $permissionId = $permission['auth_role_permission_permission_id'];
            $parameter = $permission['auth_role_permission_parameter'] ?? 0;

            $hashData[$permissionId] = $parameter;
            $permissionIds[] = $permissionId;

            $this->addToPermissionIndex($permissionId, $roleId);
        }

        $this->redis->pipeline(function ($pipe) use ($key, $hashData, $roleId, $permissionIds, $ttl) {
            $pipe->del($key);

            if (!empty($hashData))
                $pipe->hmset($key, $hashData);

            $pipe->expire($key, $ttl);

            $activeKey = $this->getPermissionActiveKey();
            $pipe->sadd($activeKey, $roleId);
            $pipe->expire($activeKey, $ttl);

            foreach ($permissionIds as $permissionId) {
                $indexKey = $this->getRolePermissionsByPermissionKey($permissionId);
                $pipe->expire($indexKey, $ttl);
            }
        });
    }

    /**
     * Add a role to the permission index in Redis.
     */
    private function addToPermissionIndex(string $permissionId, string $roleId): void {
        $key = $this->getRolePermissionsByPermissionKey($permissionId);
        $ttl = $this->getExpiredAtCache();
        $this->redis->sadd($key, $roleId);
        $this->redis->expire($key, $ttl);
    }

    /**
     * Clear the cached permission check for a specific role-permission pair.
     */
    private function clearPermissionCheckCache(string $roleId, string $permissionId): void {
        $key = $this->getPermissionCheckKey($roleId, $permissionId);
        $this->redis->del($key);
    }

    /**
     * Clear all cached permission checks for a given role.
     */
    private function clearAllPermissionCheckCache(string $roleId): void {
        $pattern = $this->getAllPermissionsCheckKey($roleId);
        $keys = $this->redis->keys($pattern);

        if (!empty($keys))
            $this->redis->del(...$keys);
    }

    /**
     * Invalidate all active user sessions associated with a given role.
     */
    private function invalidateUserSessions(string $roleId): void {
        $this->redis->publish('role-permissions-updated', json_encode([
            'role_id' => $roleId,
            'timestamp' => time(),
        ]));
    }

    /**
     * Group by role_id
     */
    private function groupRolePermissionsByRoleId(array $permissions): array {
        $grouped = [];
        foreach ($permissions as $permission) {
            $roleId = $permission['auth_role_permission_role_id'];

            $grouped[$roleId][] = [
                'auth_role_permission_permission_id' => $permission['auth_role_permission_permission_id'],
                'auth_role_permission_parameter' => $permission['auth_role_permission_parameter'],
            ];
        }

        return $grouped;
    }

    /**
     * Get expired at cache TTL
     */
    private function getExpiredAtCache(?int $payloadExp = null): int {
        if (!$payloadExp) {
            $expireAt = now()->endOfDay();
            $ttl = now()->diffInSeconds($expireAt, false);

            if ($ttl < 0) {
                $expireAt = now()->addDay()->endOfDay();
                $ttl = now()->diffInSeconds($expireAt, false);
            }

            $ttl = (int) $ttl;
            if ($ttl <= 0)
                return $this->ttl;

            return max($ttl, 60);
        }

        $expireAt = Carbon::createFromTimestamp($payloadExp);
        $ttl = now()->diffInSeconds($expireAt, false);

        $ttl = (int) $ttl;
        if ($ttl <= 0)
            return $this->ttl;

        return max($ttl, 0);
    }

    /**
     * Format raw hash data into a structured permissions array.
     */
    private function formatHashPermissions(array $hashData): array {
        $permissions = [];

        foreach ($hashData as $permissionId => $parameter) {
            $permissions[] = [
                'auth_role_permission_permission_id' => $permissionId,
                'auth_role_permission_parameter' => $parameter !== '' ? (int) $parameter : null,
            ];
        }

        return $permissions;
    }
}

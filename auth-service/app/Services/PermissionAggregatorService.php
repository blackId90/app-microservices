<?php

namespace App\Services;

use App\Enums\PermissionTypeEnum;
use Illuminate\Database\Eloquent\Collection;

class PermissionAggregatorService {
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected PermissionCacheService $permissionService,
        protected RolePermissionCacheService $rolePermissionService
    ) {
    }

    /**
     * Get aggregated permissions for a role
     *
     * @param string $roleId
     * @return Collection
     */
    public function getAggregatedPermissions(string $roleId): Collection {
        //* Get role permissions with parameters
        $rolePermissions = $this->rolePermissionService->getRolePermissions($roleId);
        if (empty($rolePermissions))
            return new Collection();

        //* Extract permission IDs
        $permissionIds = collect($rolePermissions)->pluck('auth_role_permission_permission_id')->toArray();

        //* Get detailed permission information
        $permissions = $this->permissionService->getMultiplePermissions($permissionIds);
        if ($permissions->isEmpty())
            return new Collection();

        //* Create a lookup map for permissions
        $permissionMap = $permissions->keyBy('auth_permission_id');

        //* Create a lookup map for role permissions (for parameters)
        $rolePermissionMap = collect($rolePermissions)->keyBy('auth_role_permission_permission_id');

        //* Process and aggregate permissions into Eloquent Collection
        $aggregatedPermissions = new Collection();

        foreach ($permissions as $permission) {
            //* Get the permission parameter from role permissions
            $rolePermission = $rolePermissionMap->get($permission->auth_permission_id);
            $parameter = $rolePermission['auth_role_permission_parameter'] ?? null;

            //* Get parent permission details if exists
            $parentPermission = null;
            if ($permission->auth_permission_parent_permission_id)
                $parentPermission = $permissionMap->get($permission->auth_permission_parent_permission_id);

            //* Create object for Eloquent Collection
            $aggregatedPermission = (object) [
                'permission_id' => $permission->auth_permission_id,
                'permission_type' => $permission->auth_permission_type,
                'permission_parent' => $parentPermission ? (object) [
                    'permission_id' => $parentPermission->auth_permission_id,
                    'permission_slug' => $parentPermission->auth_permission_slug,
                    'permission_title' => $parentPermission->auth_permission_title,
                ] : (object) [
                    'permission_id' => null,
                    'permission_slug' => null,
                    'permission_title' => null,
                ],
                'permission_slug' => $permission->auth_permission_slug,
                'permission_title' => $permission->auth_permission_title,
                'permission_icon' => $permission->auth_permission_icon,
                'permission_color' => $permission->auth_permission_color,
                'permission_url' => $permission->auth_permission_url,
                'permission_route' => $permission->auth_permission_route,
                'permission_target' => $permission->auth_permission_target,
                'permission_order' => $permission->auth_permission_order,
                'permission_parameter' => $parameter,
                'permission_is_active' => $permission->auth_permission_is_active,
                'created_at' => $permission->created_at,
                'updated_at' => $permission->updated_at,
            ];

            $aggregatedPermissions->push($aggregatedPermission);
        }

        //* Return aggregated permissions
        return $aggregatedPermissions;
    }

    /**
     * Get menu structure permissions for a role
     *
     * @param string $roleId
     * @return array
     */
    public function getMenuPermissions(string $roleId): array {
        $aggregatedPermissions = $this->getAggregatedPermissions($roleId);
        if ($aggregatedPermissions->isEmpty())
            return [];

        //* Filter only parent, group, and item permissions for menu
        $menuPermissions = $aggregatedPermissions->filter(function ($permission) {
            return in_array($permission->permission_type, [
                PermissionTypeEnum::GROUP->value,
                PermissionTypeEnum::PARENT->value,
                PermissionTypeEnum::ITEM->value
            ]);
        });

        //* Build hierarchical menu structure
        $menuStructure = [];

        //* First, get groups
        $groups = $menuPermissions->where('permission_type', PermissionTypeEnum::GROUP->value)->values();

        foreach ($groups as $group) {
            $groupId = $group->permission_id;

            //* Find parent permissions in this group
            $parents = $menuPermissions->filter(function ($permission) use ($groupId) {
                return $permission->permission_type === PermissionTypeEnum::PARENT->value && isset($permission->permission_group->permission_id) && $permission->permission_group->permission_id === $groupId;
            })->values();

            $groupWithParents = (array) $group;
            $groupWithParents['parents'] = [];

            foreach ($parents as $parent) {
                $parentId = $parent->permission_id;

                //* Find item permissions under this parent
                $items = $menuPermissions->filter(function ($permission) use ($parentId) {
                    return $permission->permission_type === PermissionTypeEnum::ITEM->value && isset($permission->permission_parent->permission_id) && $permission->permission_parent->permission_id === $parentId;
                })->values();

                $parentWithItems = (array) $parent;
                $parentWithItems['items'] = $items->map(function ($item) {
                    return (array) $item;
                })->toArray();

                $groupWithParents['parents'][] = $parentWithItems;
            }

            $menuStructure[] = $groupWithParents;
        }

        //* Add standalone parent permissions (not in any group)
        $standaloneParents = $menuPermissions->filter(function ($permission) {
            return $permission->permission_type === PermissionTypeEnum::PARENT->value && empty($permission->permission_group);
        })->values();

        foreach ($standaloneParents as $parent) {
            $parentId = $parent->permission_id;

            //* Find item permissions under this parent
            $items = $menuPermissions->filter(function ($permission) use ($parentId) {
                return $permission->permission_type === PermissionTypeEnum::ITEM->value && isset($permission->permission_parent->permission_id) && $permission->permission_parent->permission_id === $parentId;
            })->values();

            $parentWithItems = (array) $parent;
            $parentWithItems['items'] = $items->map(function ($item) {
                return (array) $item;
            })->toArray();

            $menuStructure[] = $parentWithItems;
        }

        return $menuStructure;
    }

    /**
     * Check if role has specific permission
     *
     * @param string $roleId
     * @param string $permissionSlug
     * @return bool
     */
    public function hasPermission(string $roleId, string $permissionSlug): bool {
        $aggregatedPermissions = $this->getAggregatedPermissions($roleId);

        return $aggregatedPermissions->contains('permission_slug', $permissionSlug);
    }

    /**
     * Get permission parameter for a specific permission
     *
     * @param string $roleId
     * @param string $permissionSlug
     * @return string|null
     */
    public function getPermissionParameter(string $roleId, string $permissionSlug): ?string {
        $aggregatedPermissions = $this->getAggregatedPermissions($roleId);

        $permission = $aggregatedPermissions->firstWhere('permission_slug', $permissionSlug);

        return $permission->permission_parameter ?? null;
    }

    /**
     * Get all permissions with their parameters for a role
     *
     * @param string $roleId
     * @return array
     */
    public function getPermissionsWithParameters(string $roleId): array {
        $aggregatedPermissions = $this->getAggregatedPermissions($roleId);

        $result = [];
        foreach ($aggregatedPermissions as $permission) {
            $result[$permission->permission_slug] = $permission->permission_parameter;
        }

        return $result;
    }

    /**
     * Get flat list of permission slugs for a role
     *
     * @param string $roleId
     * @return array
     */
    public function getPermissionSlugs(string $roleId): array {
        $aggregatedPermissions = $this->getAggregatedPermissions($roleId);

        return $aggregatedPermissions->pluck('permission_slug')->toArray();
    }

    /**
     * Get permissions by type for a role
     *
     * @param string $roleId
     * @param PermissionTypeEnum $type
     * @return Collection
     */
    public function getPermissionsByType(string $roleId, PermissionTypeEnum $type): Collection {
        $aggregatedPermissions = $this->getAggregatedPermissions($roleId);

        return $aggregatedPermissions->filter(function ($permission) use ($type) {
            return $permission->permission_type === $type->value;
        })->values();
    }

    /**
     * Filter active permissions only
     *
     * @param string $roleId
     * @return Collection
     */
    public function getActivePermissions(string $roleId): Collection {
        $aggregatedPermissions = $this->getAggregatedPermissions($roleId);

        return $aggregatedPermissions->filter(function ($permission) {
            return $permission->permission_is_active === true;
        })->values();
    }

    /**
     * Get permissions for API resource transformation
     *
     * @param string $roleId
     * @return Collection
     */
    public function getPermissionsForApi(string $roleId): Collection {
        $aggregatedPermissions = $this->getAggregatedPermissions($roleId);

        /*
        $cleanedPermissions = new Collection();
        foreach ($aggregatedPermissions as $permission) {
            $cleanedPermission = clone $permission;
            unset($cleanedPermission->created_at, $cleanedPermission->updated_at);
            $cleanedPermissions->push($cleanedPermission);
        }

        return $cleanedPermissions;
        */
        return $aggregatedPermissions;
    }

    /**
     * Get permissions by parent ID
     *
     * @param string $roleId
     * @param string|null $parentId
     * @return Collection
     */
    public function getPermissionsByParentId(string $roleId, ?string $parentId = null): Collection {
        $aggregatedPermissions = $this->getAggregatedPermissions($roleId);

        return $aggregatedPermissions->filter(function ($permission) use ($parentId) {
            if ($parentId === null)
                return $permission->permission_parent->permission_id === null;

            return $permission->permission_parent->permission_id === $parentId;
        })->values();
    }

    /**
     * Get permissions by group ID
     *
     * @param string $roleId
     * @param string|null $groupId
     * @return Collection
     */
    public function getPermissionsByGroupId(string $roleId, ?string $groupId = null): Collection {
        $aggregatedPermissions = $this->getAggregatedPermissions($roleId);

        return $aggregatedPermissions->filter(function ($permission) use ($groupId) {
            if ($groupId === null)
                return $permission->permission_group === null;

            return isset($permission->permission_group->permission_id) && $permission->permission_group->permission_id === $groupId;
        })->values();
    }

    /**
     * Search permissions by title or slug
     *
     * @param string $roleId
     * @param string $searchTerm
     * @return Collection
     */
    public function searchPermissions(string $roleId, string $searchTerm): Collection {
        $aggregatedPermissions = $this->getAggregatedPermissions($roleId);

        return $aggregatedPermissions->filter(function ($permission) use ($searchTerm) {
            return stripos($permission->permission_title, $searchTerm) !== false || stripos($permission->permission_slug, $searchTerm) !== false;
        })->values();
    }

    /**
     * Get unique permission types available for a role
     *
     * @param string $roleId
     * @return array
     */
    public function getAvailablePermissionTypes(string $roleId): array {
        $aggregatedPermissions = $this->getAggregatedPermissions($roleId);

        return $aggregatedPermissions->pluck('permission_type')->unique()->values()->toArray();
    }

    /**
     * Get permission count by type
     *
     * @param string $roleId
     * @return array
     */
    public function getPermissionCountByType(string $roleId): array {
        $aggregatedPermissions = $this->getAggregatedPermissions($roleId);

        $counts = [];
        foreach ($aggregatedPermissions as $permission) {
            $type = $permission->permission_type;
            if (!isset($counts[$type]))
                $counts[$type] = 0;

            $counts[$type]++;
        }

        return $counts;
    }

    /**
     * Check if role has any permission with parameter
     *
     * @param string $roleId
     * @return bool
     */
    public function hasAnyPermissionWithParameter(string $roleId): bool {
        $aggregatedPermissions = $this->getAggregatedPermissions($roleId);

        return $aggregatedPermissions->contains(function ($permission) {
            return $permission->permission_parameter !== null;
        });
    }

    /**
     * Get permissions grouped by parent
     *
     * @param string $roleId
     * @return array
     */
    public function getPermissionsGroupedByParent(string $roleId): array {
        $aggregatedPermissions = $this->getAggregatedPermissions($roleId);

        $grouped = [];
        foreach ($aggregatedPermissions as $permission) {
            $parentId = $permission->permission_parent->permission_id ?? 'null';

            if (!isset($grouped[$parentId])) {
                $grouped[$parentId] = [
                    'parent' => $permission->permission_parent,
                    'permissions' => new Collection()
                ];
            }

            $grouped[$parentId]['permissions']->push($permission);
        }

        return $grouped;
    }
}

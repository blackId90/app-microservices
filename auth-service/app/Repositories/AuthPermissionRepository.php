<?php

namespace App\Repositories;

use App\Enums\{PermissionTypeEnum, TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum};
use App\Models\AuthPermission;
use App\Repositories\Interfaces\AuthPermissionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
// use Illuminate\Support\Facades\DB;

class AuthPermissionRepository implements AuthPermissionRepositoryInterface {
    private array $searchColumns = ['auth_permissions.auth_permission_slug', 'auth_permissions.auth_permission_title', 'auth_permissions.auth_permission_route', 'auth_permissions_parent.auth_permission_slug', 'auth_permissions_parent.auth_permission_title', 'auth_permissions_parent.auth_permission_route'];

    public function paginateWithSearchAndType(?string $search, int $perPage = 10, int $typeList = TypeBrowseEnum::WITHOUT_DELETED, array $filterWhereIn = [], string $orderBy = 'auth_permissions.created_at', string $orderDirection = 'asc'): LengthAwarePaginator {
        return AuthPermission::query()
            ->leftJoin('auth_permissions as auth_permissions_parent', function ($join) {
                $join->on('auth_permissions.auth_permission_parent_permission_id', '=', 'auth_permissions_parent.auth_permission_id');
                // ->whereNull('auth_permissions_parent.deleted_at'); // Filter Trash untuk Parent
            })
            ->select([
                'auth_permissions.*',
                'auth_permissions_parent.auth_permission_id as auth_permission_parent_id',
                'auth_permissions_parent.auth_permission_type as auth_permission_parent_type',
                'auth_permissions_parent.auth_permission_slug as auth_permission_parent_slug',
                'auth_permissions_parent.auth_permission_title as auth_permission_parent_title',
                'auth_permissions_parent.auth_permission_route as auth_permission_parent_route',
                'auth_permissions_parent.created_at as auth_permission_parent_created_at',
                'auth_permissions_parent.updated_at as auth_permission_parent_updated_at',
                'auth_permissions_parent.deleted_at as auth_permission_parent_deleted_at'
            ])
            ->withFilterTypeList($typeList)
            ->filterWhereIn($filterWhereIn)
            ->filterSearch($search, $this->searchColumns)
            ->orderBy($orderBy, $orderDirection)
            ->paginate($perPage);
    }

    public function create(array $data): AuthPermission {
        return AuthPermission::create($data);
    }

    public function findById(string $authPermissionId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, bool $withTrash = false, array $relations = []): ?AuthPermission {
        // dd($relations);
        return AuthPermission::query()
            ->withTrashedRelations($relations)
            /*
            ->leftJoin('auth_permissions as auth_permissions_parent', function ($join) {
                $join->on('auth_permissions.auth_permission_parent_permission_id', '=', 'auth_permissions_parent.auth_permission_id');
            })
            ->select([
                'auth_permissions.*',
                'auth_permissions_parent.auth_permission_id as auth_permission_parent_id',
                'auth_permissions_parent.auth_permission_type as auth_permission_parent_type',
                'auth_permissions_parent.auth_permission_slug as auth_permission_parent_slug',
                'auth_permissions_parent.auth_permission_title as auth_permission_parent_title',
                'auth_permissions_parent.auth_permission_route as auth_permission_parent_route',
                'auth_permissions_parent.created_at as auth_permission_parent_created_at',
                'auth_permissions_parent.updated_at as auth_permission_parent_updated_at',
                'auth_permissions_parent.deleted_at as auth_permission_parent_deleted_at'
            ])
            */
            ->withFilterRead($typeRead, $withTrash)
            ->findOrFail($authPermissionId);
    }

    public function update(AuthPermission $permission, array $data): AuthPermission {
        $permission->update($data);

        return $permission;
    }

    public function delete(AuthPermission $permission, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): bool {
        return $permission->performDeleteAction($typeDelete);
    }

    public function existsBySlug(string $slug, ?string $ignoreId = null): bool {
        $query = AuthPermission::withTrashed()
            ->where('auth_permission_slug', $slug);

        if ($ignoreId)
            $query->where('auth_permission_id', '!=', $ignoreId);

        return $query->exists();
    }

    public function existsPermissionOrder(array $data, ?string $ignoreId = null): bool {
        // DB::enableQueryLog();
        $headers = [PermissionTypeEnum::HEADER->value, PermissionTypeEnum::GROUP->value, PermissionTypeEnum::PARENT->value];

        $query = AuthPermission::withTrashed()
            ->where('auth_permission_parent_permission_id', $data['auth_permission_parent_permission_id']);

        //* permission_order
        if (!empty($data['auth_permission_order']))
            $query->where('auth_permission_order', $data['auth_permission_order']);

        //* permission_type
        if (in_array($data['auth_permission_type'], $headers)) {
            $query->whereIn('auth_permission_type', $headers);
        } else {
            $query->where('auth_permission_type', $data['auth_permission_type']);
        }

        //* ignore field permission_id
        if ($ignoreId)
            $query->where('auth_permission_id', '!=', $ignoreId);

        // dd($query->exists(), DB::getQueryLog());
        return $query->exists();
    }

    public function getAllPermissionsActive(): Collection {
        return AuthPermission::active()->get();
    }

    public function findPermissionActiveByPermissionId(string $authPermissionId): ?AuthPermission {
        return AuthPermission::active()
            ->where('auth_permission_id', $authPermissionId)
            ->first();
    }

    public function findPermissionActiveBySlug(string $authRoleSlug): ?AuthPermission {
        return AuthPermission::active()
            ->where('auth_permission_slug', $authRoleSlug)
            ->first();
    }

    public function getPermissionActiveByIds(array $authPermissionId): Collection {
        return AuthPermission::active()
            ->whereIn('auth_permission_id', $authPermissionId)
            ->get();
    }
}

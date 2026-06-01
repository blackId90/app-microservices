<?php

namespace App\Repositories;

use App\Enums\{TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum};
use App\Models\AuthRole;
use App\Repositories\Interfaces\AuthRoleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AuthRoleRepository implements AuthRoleRepositoryInterface {
    private array $searchColumns = ['auth_roles.auth_role_name', 'auth_roles.auth_role_slug'];

    public function paginateWithSearchAndType(?string $search, int $perPage = 10, int $typeList = TypeBrowseEnum::WITHOUT_DELETED, array $filterWhereIn = [], string $orderBy = 'auth_roles.created_at', string $orderDirection = 'asc'): LengthAwarePaginator {
        return AuthRole::query()
            ->withFilterTypeList($typeList)
            ->filterWhereIn($filterWhereIn)
            ->filterSearch($search, $this->searchColumns)
            ->orderBy($orderBy, $orderDirection)
            ->paginate($perPage);
    }

    public function create(array $data): AuthRole {
        return AuthRole::create($data);
    }

    public function findById(string $authRoleId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, bool $withTrash = false, array $relations = []): ?AuthRole {
        return AuthRole::query()
            ->withTrashedRelations($relations)
            ->withFilterRead($typeRead, $withTrash)
            ->findOrFail($authRoleId);
    }

    /*
    public function update(string $authRoleId, array $data, int $typeUpdate = TypeUpdateEnum::WITHOUT_DELETED): AuthRole {
        $query = AuthRole::query();

        if ($typeUpdate === TypeUpdateEnum::WITH_DELETED)
            $query->withTrashed();

        $role = $query->findOrFail($authRoleId);
        $role->update($data);

        return $role->fresh();
    }
    */
    public function update(AuthRole $role, array $data): AuthRole {
        $role->update($data);

        return $role;
    }

    public function delete(AuthRole $role, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): bool {
        return $role->performDeleteAction($typeDelete);
    }

    public function existsBySlug(string $slug, ?string $ignoreId = null): bool {
        $query = AuthRole::withTrashed()
            ->where('auth_role_slug', $slug);

        if ($ignoreId)
            $query->where('auth_role_id', '!=', $ignoreId);

        return $query->exists();
    }

    //! Rename method
    public function findByRoleId(string $authRoleId): ?AuthRole {
        return AuthRole::active()
            ->where('auth_role_id', $authRoleId)
            ->first();
    }

    public function getRoleActiveByIds(array $authRoleId): Collection {
        return AuthRole::active()
            ->whereIn('auth_role_id', $authRoleId)
            ->get();
    }

    public function findBySlug(string $authRoleSlug): ?AuthRole {
        return AuthRole::active()
            ->where('auth_role_slug', $authRoleSlug)
            ->first();
    }
}

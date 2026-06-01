<?php

namespace App\Repositories\Interfaces;

use App\Enums\{TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum};
use App\Models\AuthRole;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AuthRoleRepositoryInterface {

    public function paginateWithSearchAndType(?string $search, int $perPage, int $typeList = TypeBrowseEnum::WITHOUT_DELETED, array $filterWhereIn = [], string $orderBy = 'auth_roles.created_at', string $orderDirection = 'asc'): LengthAwarePaginator;

    public function create(array $data): AuthRole;

    public function findById(string $authRoleId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, bool $withTrash = false, array $relations = []): ?AuthRole;

    // public function update(string $authRoleId, array $data, int $typeUpdate = TypeUpdateEnum::WITHOUT_DELETED): AuthRole;
    public function update(AuthRole $role, array $data): AuthRole;

    public function delete(AuthRole $role, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): bool;

    public function existsBySlug(string $slug, ?string $ignoreId = null): bool;

    //! Rename method
    public function findByRoleId(string $authRoleId): ?AuthRole;

    public function findBySlug(string $authRoleSlug): ?AuthRole;

    public function getRoleActiveByIds(array $authRoleId): Collection;
}

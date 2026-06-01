<?php

namespace App\Repositories\Interfaces;

use App\Enums\{TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum};
use App\Models\AuthPermission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AuthPermissionRepositoryInterface {

    public function paginateWithSearchAndType(?string $search, int $perPage = 10, int $typeList = TypeBrowseEnum::WITHOUT_DELETED, array $filterWhereIn = [], string $orderBy = 'auth_permissions.created_at', string $orderDirection = 'asc'): LengthAwarePaginator;

    public function create(array $data): AuthPermission;

    public function findById(string $authPermissionId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, bool $withTrash = false, array $relations = []): ?AuthPermission;

    public function update(AuthPermission $permission, array $data): AuthPermission;

    public function delete(AuthPermission $permission, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): bool;

    public function existsBySlug(string $slug, ?string $ignoreId = null): bool;

    public function existsPermissionOrder(array $data, ?string $ignoreId = null): bool;

    public function getAllPermissionsActive(): Collection;

    public function findPermissionActiveByPermissionId(string $authPermissionId): ?AuthPermission;

    public function findPermissionActiveBySlug(string $authRoleSlug): ?AuthPermission;

    public function getPermissionActiveByIds(array $authPermissionId): Collection;
}

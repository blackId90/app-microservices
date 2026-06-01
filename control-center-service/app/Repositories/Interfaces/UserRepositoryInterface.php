<?php

namespace App\Repositories\Interfaces;

use App\Enums\{TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum};
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface {

    public function paginateWithSearchAndType(?string $search, int $perPage, int $typeList = TypeBrowseEnum::WITHOUT_DELETED, string $orderBy = 'users.created_at', string $orderDirection = 'asc'): LengthAwarePaginator;

    public function createUser(array $payloads): User;

    public function findById(string $authRoleId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, bool $withTrash = false, array $relations = []): ?User;

    public function updateUser(User $user, array $payloads): User;

    public function delete(User $user, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): bool;

    public function rollbackToOldData(array $oldData): void;

    public function existsByAuthUserId(string $authUserId, ?string $ignoreId = null): bool;

    public function existsByPhone(string $userPhone, ?string $ignoreId = null): bool;

    public function findByUserId(string $userId);

    public function findByAuthUserId(string $authUserId);

    public function findUsersByKeyIds(array $ids, ?string $key = 'user_id');
}

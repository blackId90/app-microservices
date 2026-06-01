<?php

namespace App\Repositories\Interfaces;

use App\Enums\{TypeDeleteEnum, TypeReadEnum};
use App\Models\AuthUser;

interface AuthUserRepositoryInterface {

    public function create(array $data): AuthUser;

    public function findById(string $authUserId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, bool $withTrash = false, array $relations = []): ?AuthUser;

    public function update(AuthUser $authUser, array $data): AuthUser;

    public function delete(AuthUser $auhtUser, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): bool;

    public function existsByEmail(string $email, ?string $ignoreId = null): bool;

    public function existsByUsername(string $username, ?string $ignoreId = null): bool;

    public function findByIdentifier(string $identifier): ?AuthUser;

    public function findByEmail(string $email): ?AuthUser;

    public function findByUsername(string $username): ?AuthUser;
}

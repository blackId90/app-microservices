<?php

namespace App\Repositories;

use App\Enums\{TypeDeleteEnum, TypeReadEnum};
use App\Models\AuthUser;
use App\Repositories\Interfaces\AuthUserRepositoryInterface;

class AuthUserRepository implements AuthUserRepositoryInterface {

    public function create(array $data): AuthUser {
        $user = new AuthUser([
            'auth_user_email' => $data['auth_user_email'],
            'auth_user_username' => $data['auth_user_username'],
            'auth_user_company_id' => $data['auth_user_company_id'] ?? null,
            'auth_user_password' => $data['auth_user_password']
        ]);

        $user->auth_user_role_id = $data['auth_user_role_id'];
        $user->auth_user_is_admin = $data['auth_user_is_admin'];

        $user->save();

        return $user;
    }

    public function findById(string $authUserId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, bool $withTrash = false, array $relations = []): ?AuthUser {
        return AuthUser::query()
            ->withTrashedRelations($relations)
            ->withFilterRead($typeRead, $withTrash)
            ->findOrFail($authUserId);
    }

    public function update(AuthUser $authUser, array $data): AuthUser {
        if (isset($data['auth_user_is_admin']))
            $authUser->auth_user_is_admin = $data['auth_user_is_admin'];

        if (isset($data['auth_user_is_status']))
            $authUser->auth_user_is_status = $data['auth_user_is_status'];

        $authUser->auth_user_role_id = $data['auth_user_role_id'];

        $authUser->update($data);

        return $authUser;
    }

    public function delete(AuthUser $authUser, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): bool {
        return $authUser->performDeleteAction($typeDelete);
    }

    public function existsByEmail(string $email, ?string $ignoreId = null): bool {
        $query = AuthUser::withTrashed()
            ->where('auth_user_email', $email);

        if ($ignoreId)
            $query->where('auth_user_id', '!=', $ignoreId);

        return $query->exists();
    }

    public function existsByUsername(string $username, ?string $ignoreId = null): bool {
        $query = AuthUser::withTrashed()
            ->where('auth_user_username', $username);

        if ($ignoreId)
            $query->where('auth_user_id', '!=', $ignoreId);

        return $query->exists();
    }

    public function findByIdentifier(string $identifier): ?AuthUser {
        /*
        return AuthUser::with('role')
            ->where('auth_user_email', $identifier)
            ->orWhere('auth_user_username', $identifier)
            ->first();
        */

        return AuthUser::where('auth_user_email', $identifier)
            ->orWhere('auth_user_username', $identifier)
            ->first();
    }

    public function findByEmail(string $email): ?AuthUser {
        return AuthUser::where('auth_user_email', $email)->first();
    }

    public function findByUsername(string $username): ?AuthUser {
        return AuthUser::where('auth_user_username', $username)->first();
    }
}

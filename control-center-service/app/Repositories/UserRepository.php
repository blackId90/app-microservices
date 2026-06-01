<?php

namespace App\Repositories;

use App\Enums\{TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum};
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class UserRepository implements UserRepositoryInterface {
    private array $searchColumns = ['users.user_first_name', 'users.user_last_name', 'sync_auth_users.auth_user_email', 'sync_auth_users.auth_user_username'];

    public function paginateWithSearchAndType(?string $search, int $perPage = 10, int $typeList = TypeBrowseEnum::WITHOUT_DELETED, string $orderBy = 'users.created_at', string $orderDirection = 'asc'): LengthAwarePaginator {
        return User::query()
            ->leftJoin('sync_auth_users', function ($join) {
                $join->on('users.user_auth_user_id', '=', 'sync_auth_users.auth_user_id')
                    ->whereNull('sync_auth_users.deleted_at'); // Filter trash for table relations
            })
            ->leftJoin('companies', function ($join) {
                $join->on('sync_auth_users.auth_user_company_id', '=', 'companies.company_id')
                    ->whereNull('companies.deleted_at');
            })
            ->select([
                'users.*',

                //* sync_auth_users
                'sync_auth_users.auth_user_id as sync_auth_users_auth_user_id',
                'sync_auth_users.auth_user_email as sync_auth_users_auth_user_email',
                'sync_auth_users.auth_user_username as sync_auth_users_auth_user_username',
                'sync_auth_users.auth_user_company_id as sync_auth_users_auth_user_company_id',
                'sync_auth_users.auth_user_is_admin as sync_auth_users_auth_user_is_admin',
                'sync_auth_users.auth_user_is_status as sync_auth_users_auth_user_is_status',
                'sync_auth_users.created_at as sync_auth_users_created_at',
                'sync_auth_users.updated_at as sync_auth_users_updated_at',
                'sync_auth_users.deleted_at as sync_auth_users_deleted_at',

                //* companies
                'companies.company_id as companies_company_id',
                'companies.company_logo as companies_company_logo',
                'companies.company_name as companies_company_name',
                'companies.created_at as companies_created_at',
                'companies.updated_at as companies_updated_at',
                'companies.deleted_at as companies_deleted_at',
            ])
            ->withFilterTypeList($typeList)
            ->filterSearch($search, $this->searchColumns)
            ->orderBy($orderBy, $orderDirection)
            ->paginate($perPage);
    }

    /**
     * Create new user associated with company
     *
     * @param array $payloads
     * @return User
     */
    public function createUser(array $payloads): User {
        $user = User::create([
            'user_auth_user_id' => $payloads['user_auth_user_id'],
            'user_first_name' => $payloads['user_first_name'],
            'user_last_name' => $payloads['user_last_name'],
            'user_gender' => $payloads['user_gender'],
            'user_address' => $payloads['user_address'] ?? null,
            'user_village_id' => $payloads['user_village_id'] ?? null,
            'user_zip_code' => $payloads['user_zip_code'] ?? null,
            'user_phone' => $payloads['user_phone'] ?? null,
        ]);

        return $user;
    }

    public function findById(string $authRoleId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, bool $withTrash = false, array $relations = []): ?User {
        return User::query()
            ->withTrashedRelations($relations)
            ->withFilterRead($typeRead, $withTrash)
            ->find($authRoleId); // ->findOrFail($authRoleId);
    }

    public function updateUser(User $user, array $payloads): User {
        $user->update($payloads);

        return $user;
    }

    public function delete(User $user, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): bool {
        return $user->performDeleteAction($typeDelete);
    }

    public function rollbackToOldData(array $oldData): void {
        // return DB::table('users')->insert($oldData);
        DB::table('users')->upsert(
            [$oldData], // Data snapshot lama
            ['user_id'], // Unique key untuk deteksi (Primary Key)
            array_keys($oldData) // Update semua kolom jika data sudah ada
        );
    }

    public function existsByAuthUserId(string $authUserId, ?string $ignoreId = null): bool {
        $query = User::withTrashed()
            ->without(['village.district.regency.province'])
            ->where('user_auth_user_id', $authUserId);

        if ($ignoreId)
            $query->where('user_id', '!=', $ignoreId);

        return $query->exists();
    }

    public function existsByPhone(string $userPhone, ?string $ignoreId = null): bool {
        $query = User::withTrashed()
            ->without(['village.district.regency.province'])
            ->where('user_phone', $userPhone);

        if ($ignoreId)
            $query->where('user_id', '!=', $ignoreId);

        return $query->exists();
    }

    public function findByUserId(string $userId) {
        $userData = User::where('user_id', $userId)
            ->first();

        return $userData;
    }

    public function findByAuthUserId(string $authUserId) {
        /*
        $userData = User::with([
            //* Nested eager loading for location region
            'village.district.regency.province',

            //* Company
            // 'company' => function ($query) use ($reqCompanyId) {
            //     if ($reqCompanyId)
            //         $query->where('company_id', $reqCompanyId);

            //     $query->with(['detail', 'appAuthentication']);
            // },
        ])->where('user_auth_user_id', $reqAuthUserId)->first();
        */

        $userData = User::where('user_auth_user_id', $authUserId)
            ->first();

        return $userData;
    }

    public function findUsersByKeyIds(array $ids, ?string $key = 'user_id') {
        $userDatas = User::whereIn($key, $ids)
            ->get();

        return $userDatas;
    }
}

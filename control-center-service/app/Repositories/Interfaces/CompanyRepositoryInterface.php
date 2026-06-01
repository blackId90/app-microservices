<?php

namespace App\Repositories\Interfaces;

use App\Enums\{TypeReadEnum};
use App\Models\Company;

interface CompanyRepositoryInterface {

    /**
     * Create new company
     *
     * @param array $data
     * @return Company
     */
    public function createCompany(array $data): Company;

    public function findById(string $companyId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, bool $withTrash = false, array $relations = []): ?Company;

    public function findByCompanyId(string $companyId): ?Company;

    public function findActiveVerifiedWithAppAuthenticationById(string $companyId);

    public function findActiveVerifiedWithAppAuthenticationByIds(array $companyIds);
}

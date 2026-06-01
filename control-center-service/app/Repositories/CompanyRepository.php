<?php

namespace App\Repositories;

use App\Enums\{TypeReadEnum};
use App\Models\Company;
use App\Repositories\Interfaces\CompanyRepositoryInterface;

class CompanyRepository implements CompanyRepositoryInterface {

    /**
     * Create new company with trial billing status
     *
     * @param array $data
     * @return Company
     */
    public function createCompany(array $data): Company {
        $company = new Company([
            'company_name' => $data['company_name'],
            'company_email' => $data['company_email'],
            'company_phone' => $data['company_phone'],
            'company_address' => $data['company_address'],
            'company_village_id' => $data['company_village_id'],
            'company_zip_code' => $data['company_zip_code'] ?? null,
            'company_website' => $data['company_website'] ?? null,
        ]);

        //* Manual mass assignment fields
        $company->company_is_status = $data['company_is_status'] ?? null;
        $company->company_billing_status = $data['company_billing_status'] ?? null;
        $company->company_base_price = $data['company_base_price'] ?? null;
        $company->company_billing_cycle = $data['company_billing_cycle'] ?? null;

        $company->save();

        return $company;
    }

    public function findById(string $companyId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, bool $withTrash = false, array $relations = []): ?Company {
        return Company::query()
            ->withTrashedRelations($relations)
            ->withFilterRead($typeRead, $withTrash)
            ->find($companyId); // ->findOrFail($companyId);
    }

    /**
     * Find company by ID (for destroy)
     */
    public function findByCompanyId(string $companyId): ?Company {
        return Company::find($companyId);
    }

    public function findActiveVerifiedWithAppAuthenticationById(string $companyId) {
        $companyData = Company::activeVerified()
            ->billingValid()
            ->with([
                'appAuthentication',
                // 'events' => fn($q) => $q->latestEvent(3),
                // 'invoices' => fn($q) => $q->latestPaid(3),
            ])
            ->where('company_id', $companyId)
            ->first();

        return $companyData;
    }

    public function findActiveVerifiedWithAppAuthenticationByIds(array $companyIds) {
        $companies = Company::activeVerified()
            ->billingValid()
            ->with([
                'appAuthentication',
                // 'events' => fn($q) => $q->latestEvent(3),
                // 'invoices' => fn($q) => $q->latestPaid(3),
            ])
            ->whereIn('company_id', $companyIds)
            ->get();

        return $companies;
    }
}

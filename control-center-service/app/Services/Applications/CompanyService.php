<?php

namespace App\Services\Applications;

use App\Repositories\Interfaces\CompanyRepositoryInterface;
use Illuminate\Http\Request;

class CompanyService {

    public function __construct(
        protected CompanyRepositoryInterface $companyRepository
    ) {}

    public function signinCompany(Request $request) {
        //* Get request info
        $reqCompanyId = $request->attributes->get('companyId');

        //* Get Data Company Profile
        $companyProfileData = $this->companyRepository->findActiveVerifiedWithAppAuthenticationById($reqCompanyId);

        return $companyProfileData;
    }
}

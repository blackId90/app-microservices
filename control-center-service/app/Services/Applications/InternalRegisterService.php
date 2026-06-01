<?php

namespace App\Services\Applications;

use App\Enums\AppAuthResponseCode;
use App\Enums\CompanyEventTypeEnum;
use App\Exceptions\AppControlCenterException;
use App\Models\Company;
use App\Models\CompanyEvent;
use App\Repositories\Interfaces\CompanyRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

class InternalRegisterService {

    public function __construct(
        protected CompanyRepositoryInterface $companyRepository,
        protected UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * Handle internal register (create company & user)
     * Called from Auth Service during user registration
     *
     * @param array $companyData
     * @param array $userData
     * @return array{company: \App\Models\Company, user: \App\Models\User}
     */
    public function register(array $companyData, array $userData): array {
        try {
            return DB::transaction(function () use ($companyData, $userData) {
                //* 1. Create Company using Repository
                $company = $this->companyRepository->createCompany($companyData);

                //* 2. Create Company Details using Repository
                $dataCompanyDetail = [];
                $company->details()->create($dataCompanyDetail);

                /*
                //* Manual mass assignment fields
                $detail = $company->details()->make($detailData);
                $detail->company_detail_facebook = $detailData['company_detail_facebook'];
                $detail->save();
                */

                //* 3. Create User associated with company using Repository
                $user = $this->userRepository->createUser($userData);

                //* 4. Log Company Event (provisioning)
                $this->logProvisioningEvent($company, $company->company_id, $user->user_auth_user_id);

                //* 5. Show hidden property
                // $company->makeVisible(['company_key_email']);

                return compact('company', 'user');
            });
        } catch (\Illuminate\Database\QueryException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function verifyCompanyEmail(string $id, string $hash): void {
        $company = $this->companyRepository->findByCompanyId($id);
        if (!$company)
            throw new AppControlCenterException(AppAuthResponseCode::AccountNotFound);

        //* Validasi hash email
        if (!hash_equals($hash, sha1($company->getKeyEmail())))
            throw new AppControlCenterException(AppAuthResponseCode::LinkVerificationInvalid);

        if ($company->hasVerifiedEmail())
            throw new AppControlCenterException(AppAuthResponseCode::EmailAlreadyVerified);

        $company->markEmailAsVerified();
    }

    /**
     * Handle internal destroy register (delete company & user)
     * Called from Auth Service during deprovisioning
     */
    public function destroyRegister(string $companyId, string $userAuthUserId): bool {
        if (!$companyId || !$userAuthUserId)
            throw new AppControlCenterException(AppAuthResponseCode::BadRequest);

        try {
            return DB::transaction(function () use ($companyId, $userAuthUserId) {
                //* 1. Find User using Repository
                $user = $this->userRepository->findByAuthUserId($userAuthUserId);
                if (!$user)
                    throw new AppControlCenterException(AppAuthResponseCode::NotFound);

                //* 2. Find Company using Repository
                $company = $this->companyRepository->findByCompanyId($companyId);
                if (!$company)
                    throw new AppControlCenterException(AppAuthResponseCode::NotFound);

                //* 3. Force Delete Company Events using Repository
                $company->events()->forceDelete();

                //* 4. Force Delete Company Details using Repository
                $company->details()->forceDelete();

                //* 5. Force Delete Company using Repository
                $company->forceDelete();

                //* 6. Force Delete User using Repository
                $user->forceDelete();

                //* 7. Log Company Event (deprovisioning)
                // $this->logDeprovisioningEvent($company, $user);

                //* Invalidate caches
                /*
                app(\App\Services\UserCacheService::class)->invalidateUser($userAuthUserId);
                app(\App\Services\CompanyCacheService::class)->invalidateCompany($companyId);
                */

                return true;
            });
        } catch (\Illuminate\Database\QueryException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Log provisioning event for company
     *
     * @param \App\Models\Company $company
     * @param string $companyId
     * @param string $userId
     * @return \App\Models\CompanyEvent
     */
    protected function logProvisioningEvent(Company $company, string $companyId, string $authUserId): CompanyEvent {
        $dataCompanyEvent = [
            'company_event_type' => CompanyEventTypeEnum::PROVISIONING,
            'company_event_description' => 'Company created via internal register from Auth Service',
            'company_event_metadata' => [
                'source' => 'auth_service_internal_register',
                'registered_company_id' => $companyId,
                'registered_by_user_id' => $authUserId,
            ],
            'company_event_status' => 1, // Success
            'created_by' => $authUserId,
        ];

        return $company->events()->create($dataCompanyEvent);
    }

    /**
     * Log deprovisioning event
     */
    /*
    protected function logDeprovisioningEvent(Company $company, User $user): CompanyEvent {
        $dataCompanyEvent = [
            'company_event_type' => CompanyEventTypeEnum::SUSPENSION,
            'company_event_description' => 'Failed company created via internal register from Auth Service',
            'company_event_metadata' => [
                'source' => 'auth_service_internal_register',
                'registered_company' => $company,
                'registered_user' => $user
            ],
            'company_event_status' => 1, // Success
            'created_by' => $user->user_auth_user_Id,
        ];

        return $company->events()->create($dataCompanyEvent);
    }
    */
}

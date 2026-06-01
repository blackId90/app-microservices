<?php

namespace App\Services\Applications;

// use App\Enums\AppAuthResponseCode;
// use App\Exceptions\AppControlCenterException;
use App\Exceptions\UserNotFoundFromTokenException;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\CompanyCacheService;
use App\Services\UserCacheService;
use Illuminate\Http\Request;

class AuthUserService {

    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected CompanyService $companyService,
        protected UserCacheService $userCacheService,
        protected CompanyCacheService $companyCacheService
    ) {}

    public function signinProfile(Request $request) {
        //* Get request info
        $reqIsAdmin = $request->attributes->get('isAdmin');
        $reqAuthUserId = $request->attributes->get('userId');
        $reqCompanyId = $request->attributes->get('companyId');

        //* Get Data User Profile from Redis Cache
        // $isCacheUser = $this->userCacheService->isCached($reqAuthUserId);
        $userProfileData = $this->userCacheService->getUser($reqAuthUserId);
        if (!$userProfileData)
            throw new UserNotFoundFromTokenException();

        //* Get Data Company Profile from Redis Cache
        if (!$reqIsAdmin) {
            // $isCacheCompany = $this->companyCacheService->isCached($reqCompanyId);
            $companyProfileData = $this->companyCacheService->getCompany($reqCompanyId);
        }

        return [
            // 'isCacheUser' => $isCacheUser,
            'user' => $userProfileData,
            // 'isCacheCompany' => $isCacheCompany ?? false,
            'company' => $companyProfileData ?? null
        ];
    }

    public function destroySigninProfile(Request $request): bool {
        //* Get request info
        $reqAuthUserId = $request->attributes->get('userId');

        //* Invalidate user cache (only main cache key, no need for DB query)
        $this->userCacheService->invalidateUserCacheOnly($reqAuthUserId);

        //* Get Data Company Profile from Redis Cache
        // $reqCompanyId = $request->attributes->get('companyId');

        //* Invalidate company cache (only main cache key, no need for DB query)
        // $this->companyCacheService->invalidateCompanyCacheOnly($reqCompanyId);

        return true;
    }
}

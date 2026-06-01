<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyAppAuthentication;
use App\Models\CompanyDetail;
use App\Models\RegDistrict;
use App\Models\RegProvince;
use App\Models\RegRegency;
use App\Models\RegVillage;
use App\Repositories\Interfaces\CompanyRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;

class CompanyCacheService {

    /**
     * Create a new class instance.
     */
    public function __construct(
        protected CompanyRepositoryInterface $companyRepository,
        protected ?Connection $redis = null,
        protected int $ttl = 3600, // default 1 hour
        protected string $prefix = 'cache:control-center-service:company',
        protected string $prefixData = 'data',
        protected string $prefixIdentifier = 'identifier'
    ) {
        $this->redis = Redis::connection('jwt');
        $this->ttl = config('cache.user_ttl', 3600); // 1 hour default
    }

    /**
     * Get company from cache
     */
    public function getCompany(string $companyId, ?int $payloadExp = null): ?Company {
        $key = $this->getCompanyKey($companyId);

        //* Try to get from cache
        $cached = $this->redis->get($key);
        if ($cached)
            return $this->deserializeCompany($cached);

        //* Get from database
        $company = $this->companyRepository->findActiveVerifiedWithAppAuthenticationById($companyId);
        if ($company)
            $this->cacheCompany($company, $payloadExp);

        return $company;
    }

    /**
     * Cache company data
     */
    public function cacheCompany(Company $company, ?int $payloadExp = null): void {
        $companyId = $company->getKey();
        if (!$companyId)
            throw new \InvalidArgumentException("Company ID is required for caching");

        $key = $this->getCompanyKey($companyId);
        $ttl = $this->getExpiredAtCache($payloadExp);

        //* Cache company data
        $this->redis->setex($key, $ttl, $this->serializeCompany($company));

        //* Cache email -> companyId mapping
        if ($company->company_email) {
            $emailKey = $this->getCompanyByEmailKey($company->company_email);
            $this->redis->setex($emailKey, $ttl, $companyId);
        }
    }

    /**
     * Update company in cache and database
     */
    public function updateCompany(string $companyId, array $data): ?Company {
        $company = $this->companyRepository->findActiveVerifiedWithAppAuthenticationById($companyId);
        if (!$company)
            return null;

        //* If email is being changed, invalidate old cache
        $oldEmail = $company->company_email;

        //* Update database
        $company->update($data);

        //* Invalidate old email cache if changed
        if (isset($data['company_email']) && $data['company_email'] !== $oldEmail)
            $this->invalidateCompanyByEmail($oldEmail);

        //* Refresh company from database to get updated data
        $company->refresh();

        //* Update cache with new data
        $this->cacheCompany($company);

        return $company;
    }

    /**
     * Delete company from cache (with DB query)
     */
    public function invalidateCompany(string $companyId): void {
        $company = $this->companyRepository->findActiveVerifiedWithAppAuthenticationById($companyId);

        if ($company) {
            //* Delete main cache
            $key = $this->getCompanyKey($companyId);
            $this->redis->del($key);

            //* Delete email mapping
            if ($company->auth_user_email)
                $this->invalidateCompanyByEmail($company->company_email);
        }
    }

    /**
     * Delete main company cache, email mapping cache from cache (without DB query)
     */
    public function invalidateCompanyCacheOnly(string $companyId): void {
        $key = $this->getCompanyKey($companyId);

        //* Try to get data company from cache
        $companyCached = $this->redis->get($key);
        if ($companyCached) {
            $storedCompanyData = $this->deserializeCompany($companyCached);

            //* Delete email mapping
            $email = $storedCompanyData['company_email'];
            if ($email)
                $this->invalidateCompanyByEmail($email);

            //* Delete main cache
            $this->redis->del($key);
        }
    }

    /**
     * Delete company by email from cache
     */
    public function invalidateCompanyByEmail(string $email): void {
        $emailKey = $this->getCompanyByEmailKey($email);
        $this->redis->del($emailKey);
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array {
        $keys = $this->redis->keys("{$this->prefix}:*");

        $stats = [
            'total_cached_companies' => 0,
            'email_mappings' => 0
        ];

        foreach ($keys as $key) {
            if (strpos($key, ':email:') !== false) {
                $stats['email_mappings']++;
            } else {
                $stats['total_cached_companies']++;
            }
        }

        return $stats;
    }

    /**
     * Clear all company cache
     */
    public function clearAllCache(): int {
        $keys = $this->redis->keys("{$this->prefix}:*");
        if (empty($keys))
            return 0;

        return $this->redis->del(...$keys);
    }

    /**
     * Warm up cache for multiple companies
     */
    public function warmUpCache(array $companyIds): void {
        $companies = $this->companyRepository->findActiveVerifiedWithAppAuthenticationByIds($companyIds);

        foreach ($companies as $company) {
            $this->cacheCompany($company);
        }
    }

    /**
     * Get TTL for company cache
     */
    public function getCacheTTL(string $companyId): ?int {
        $key = $this->getCompanyKey($companyId);
        $ttl = $this->redis->ttl($key);

        return $ttl > 0 ? $ttl : null;
    }

    /**
     * Refresh cache TTL for company
     */
    public function refreshCacheTTL(string $companyId): bool {
        $key = $this->getCompanyKey($companyId);
        if ($this->redis->exists($key))
            return $this->redis->expire($key, $this->ttl);

        return false;
    }

    /**
     * Check if company exists in cache
     */
    public function isCached(string $companyId): bool {
        $key = $this->getCompanyKey($companyId);

        return $this->redis->exists($key) > 0;
    }

    /**
     * Get multiple companies from cache or database (batch operation)
     */
    public function getMultipleCompanies(array $companyIds): array {
        $companies = [];
        $missingIds = [];

        //* Try to get from cache first
        foreach ($companyIds as $companyId) {
            $company = $this->getCompany($companyId);
            if ($company) {
                $companies[$companyId] = $company;
            } else {
                $missingIds[] = $companyId;
            }
        }

        //* Get missing users from database and cache them
        if (!empty($missingIds)) {
            $dbUsers = $this->companyRepository->findActiveVerifiedWithAppAuthenticationByIds($missingIds);

            foreach ($dbUsers as $company) {
                $this->cacheCompany($company);
                $companies[$company->getKey()] = $company;
            }
        }

        return $companies;
    }

    /**
     * Generate cache key for company by company_id
     */
    private function getCompanyKey(string $companyId): string {
        return "{$this->prefix}:{$this->prefixData}:{$companyId}";
    }

    /**
     * Generate cache key for company by email
     */
    private function getCompanyByEmailKey(string $email): string {
        return "{$this->prefix}:{$this->prefixIdentifier}:email:{$email}";
    }

    private function getExpiredAtCache(?int $payloadExp = null) {
        if (!$payloadExp) {
            //* Ambil waktu akhir hari ini (23:59:59)
            $expireAt = now()->endOfDay();

            //* Hitung selisih detik dari sekarang sampai jam 23:59:59
            $ttl = now()->diffInSeconds($expireAt, false);

            //* Cast to integer and make sure it is not negative
            $ttl = (int) $ttl;
            if ($ttl <= 0)
                return $this->ttl;

            return $ttl;
        }

        //* Hitung waktu expired dari payload
        $expireAt = Carbon::createFromTimestamp($payloadExp);

        //* Hitung selisih detik dari sekarang sampai jam 23:59:59
        $ttl = now()->diffInSeconds($expireAt, false);

        //* Cast to integer and make sure it is not negative
        $ttl = (int) $ttl;
        if ($ttl <= 0)
            return $this->ttl;

        return $ttl;
    }

    /**
     * Serialize company for caching
     */
    private function serializeCompany(Company $company): string {
        return json_encode([
            'id' => $company->getKey(),
            'attributes' => $company->attributesToArray(),
            'relations' => $this->serializeRelations($company->getRelations()),
        ]);
    }

    /**
     * Serialize relations recursively
     */
    private function serializeRelations(array $relations): array {
        $result = [];

        foreach ($relations as $name => $relation) {
            if ($relation instanceof Model) {
                $result[$name] = [
                    'attributes' => $relation->attributesToArray(),
                    'relations' => $this->serializeRelations($relation->getRelations()),
                ];
            } elseif ($relation instanceof Collection) {
                $result[$name] = $relation->map(function (Model $model) {
                    return [
                        'attributes' => $model->attributesToArray(),
                        'relations' => $this->serializeRelations($model->getRelations()),
                    ];
                })->toArray();
            }
        }

        return $result;
    }

    /**
     * Deserialize company from cache
     */
    private function deserializeCompany(string $data): Company {
        $companyData = json_decode($data, true);

        $company = new Company();
        $company->exists = true;
        $company->setRawAttributes($companyData['attributes']);

        foreach ($companyData['relations'] ?? [] as $relation => $value) {
            $company->setRelation($relation, $this->deserializeRelation($relation, $value));
        }

        return $company;
    }

    /**
     * Deserialize relations recursively
     */
    private function deserializeRelation(string $relation, mixed $data): mixed {
        $modelClass = match ($relation) {
            'village' => RegVillage::class,
            'district' => RegDistrict::class,
            'regency' => RegRegency::class,
            'province' => RegProvince::class,
            'details' => CompanyDetail::class,
            'appAuthentication' => CompanyAppAuthentication::class,
            // tambahkan mapping lain sesuai relasi Company
            default => null,
        };

        if (!$modelClass)
            return null;

        //* Single model relation
        if (isset($data['attributes'])) {
            $model = new $modelClass();
            $model->exists = true;
            $model->setRawAttributes($data['attributes']);

            foreach ($data['relations'] ?? [] as $nestedRelation => $nestedValue) {
                $model->setRelation($nestedRelation, $this->deserializeRelation($nestedRelation, $nestedValue));
            }

            return $model;
        }

        //* Collection relation
        if (is_array($data) && array_is_list($data)) {
            return collect($data)->map(function ($item) use ($modelClass) {
                $model = new $modelClass();
                $model->exists = true;
                $model->setRawAttributes($item['attributes']);

                foreach ($item['relations'] ?? [] as $nestedRelation => $nestedValue) {
                    $model->setRelation($nestedRelation, $this->deserializeRelation($nestedRelation, $nestedValue));
                }

                return $model;
            });
        }

        return null;
    }
}

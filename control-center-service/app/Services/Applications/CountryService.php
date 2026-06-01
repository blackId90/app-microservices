<?php

namespace App\Services\Applications;

use App\Enums\{AppAuthResponseCode, TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum, TypeUpdateEnum};
use App\Exceptions\{AppControlCenterException, ValidationFormRequestException};
use App\Models\Country;
use App\Repositories\Interfaces\CountryRepositoryInterface;
use App\Services\BaseApplicationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CountryService extends BaseApplicationService {

    public function __construct(
        protected CountryRepositoryInterface $countryRepository
    ) {}

    public function listCountries(?string $search, int $perPage, int $typeList = TypeBrowseEnum::WITHOUT_DELETED): LengthAwarePaginator {
        $this->validationQueryParamsTypeList($typeList);

        return $this->countryRepository->paginateWithSearchAndType($search, $perPage, $typeList);
    }

    public function createCountry(array $payloads): Country {
        try {
            //* 1. Check validation unique country
            $this->validationUnique($payloads);

            //* 2. Insert country
            $result = DB::transaction(function () use ($payloads) {
                return $this->countryRepository->createCountry($payloads);
            });

            return $result;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getCountryById(string $countryId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, array $relations = []): ?Country {
        $this->validationQueryParamsTypeRead($typeRead);

        try {
            //* 1. Find country
            $findData = $this->countryRepository->findCountryById($countryId, $typeRead, true, $relations);
            if (!$findData)
                throw new AppControlCenterException(AppAuthResponseCode::NotFound);

            return $findData;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function updateCountry(string $countryId, array $payloads, int $typeUpdate = TypeUpdateEnum::WITHOUT_DELETED): Country {
        $this->validationQueryParamsTypeUpdate($typeUpdate);

        try {
            //* Find data country by country_id
            $findData = $this->countryRepository->findCountryById($countryId, $typeUpdate, true);
            if (!$findData)
                throw new AppControlCenterException(AppAuthResponseCode::NotFound);

            //* Check validation unique country
            $this->validationUnique($payloads, $countryId);

            //* Update country
            $result = DB::transaction(function () use ($findData, $payloads) {
                return $this->countryRepository->updateCountry($findData, $payloads);
            });

            return $result;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function deleteCountry(string $countryId, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): array {
        $this->validationQueryParamsTypeDelete($typeDelete);

        //* Determine readType based on typeDelete
        $typeRead = $this->DetermineTypeReadBaseOnTypeDelete($typeDelete);

        try {
            $findData = $this->countryRepository->findCountryById($countryId, $typeRead);
            if (!$findData)
                throw new AppControlCenterException(AppAuthResponseCode::NotFound);

            //* Delete country
            return DB::transaction(function () use ($findData, $typeDelete) {
                $this->countryRepository->deleteCountry($findData, $typeDelete);

                //* Mapping message from typeDelete
                $message = $this->MappingMessageTypeDelete($typeDelete);

                return compact('message');
            });
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function optionCountries(?string $search, int $perPage = 10, int $typeList = TypeBrowseEnum::WITHOUT_DELETED, array $filterWhereIn = []): LengthAwarePaginator {
        $this->validationQueryParamsTypeList($typeList);

        return $this->countryRepository->paginateWithSearchAndType($search, $perPage, $typeList, $filterWhereIn);
    }

    private function validationUnique(array $payloads, ?string $ignoreId = null): void {
        if ($this->countryRepository->existsByCountryCode($payloads['country_code'], $ignoreId)) {
            throw new ValidationFormRequestException(context: [
                'country_code' => __('validation.unique', ['attribute' => __('attributes.country_code')]),
            ]);
        }

        if ($this->countryRepository->existsByCountryAlpha3($payloads['country_alpha_3'], $ignoreId)) {
            throw new ValidationFormRequestException(context: [
                'country_alpha_3' => __('validation.unique', ['attribute' => __('attributes.country_alpha_3')]),
            ]);
        }
    }
}

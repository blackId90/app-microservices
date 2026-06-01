<?php

namespace App\Services\Applications;

use App\Enums\{AppAuthResponseCode, TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum, TypeUpdateEnum};
use App\Exceptions\{AppControlCenterException, ValidationFormRequestException};
use App\Models\Currency;
use App\Repositories\Interfaces\CurrencyRepositoryInterface;
use App\Services\BaseApplicationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CurrencyService extends BaseApplicationService {

    public function __construct(
        protected CurrencyRepositoryInterface $currencyRepository
    ) {}

    public function listCurrencies(?string $search, int $perPage, int $typeList = TypeBrowseEnum::WITHOUT_DELETED): LengthAwarePaginator {
        $this->validationQueryParamsTypeList($typeList);

        return $this->currencyRepository->paginateWithSearchAndType($search, $perPage, $typeList);
    }

    public function createCurrency(array $payloads): Currency {
        try {
            //* 1. Check validation unique currency
            $this->validationUnique($payloads);

            //* 2. Insert currency
            $result = DB::transaction(function () use ($payloads) {
                return $this->currencyRepository->createCurrency($payloads);
            });

            return $result;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getCurrencyById(string $currencyId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, array $relations = []): ?Currency {
        $this->validationQueryParamsTypeRead($typeRead);

        try {
            //* 1. Find currency
            $findData = $this->currencyRepository->findCurrencyById($currencyId, $typeRead, true, $relations);
            if (!$findData)
                throw new AppControlCenterException(AppAuthResponseCode::NotFound);

            return $findData;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function updateCurrency(string $currencyId, array $payloads, int $typeUpdate = TypeUpdateEnum::WITHOUT_DELETED): Currency {
        $this->validationQueryParamsTypeUpdate($typeUpdate);

        try {
            //* Find data currency by currency_id
            $findData = $this->currencyRepository->findCurrencyById($currencyId, $typeUpdate, true);
            if (!$findData)
                throw new AppControlCenterException(AppAuthResponseCode::NotFound);

            //* Check validation unique currency
            $this->validationUnique($payloads, $currencyId);

            //* Update currency
            $result = DB::transaction(function () use ($findData, $payloads) {
                return $this->currencyRepository->updateCurrency($findData, $payloads);
            });

            return $result;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function deleteCurrency(string $currencyId, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): array {
        $this->validationQueryParamsTypeDelete($typeDelete);

        //* Determine readType based on typeDelete
        $typeRead = $this->DetermineTypeReadBaseOnTypeDelete($typeDelete);

        try {
            $findData = $this->currencyRepository->findCurrencyById($currencyId, $typeRead);
            if (!$findData)
                throw new AppControlCenterException(AppAuthResponseCode::NotFound);

            //* Delete currency
            return DB::transaction(function () use ($findData, $typeDelete) {
                $this->currencyRepository->deleteCurrency($findData, $typeDelete);

                //* Mapping message from typeDelete
                $message = $this->MappingMessageTypeDelete($typeDelete);

                return compact('message');
            });
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function optionCurrencies(?string $search, int $perPage = 10, int $typeList = TypeBrowseEnum::WITHOUT_DELETED, array $filterWhereIn = []): LengthAwarePaginator {
        $this->validationQueryParamsTypeList($typeList);

        return $this->currencyRepository->paginateWithSearchAndType($search, $perPage, $typeList, $filterWhereIn);
    }

    private function validationUnique(array $payloads, ?string $ignoreId = null): void {
        if ($this->currencyRepository->existsByCurrencyCode($payloads['currency_code'], $ignoreId)) {
            throw new ValidationFormRequestException(context: [
                'currency_code' => __('validation.unique', ['attribute' => __('attributes.currency_code')]),
            ]);
        }
    }
}

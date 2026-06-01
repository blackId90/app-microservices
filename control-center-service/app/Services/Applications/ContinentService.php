<?php

namespace App\Services\Applications;

use App\Enums\{AppAuthResponseCode, TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum, TypeUpdateEnum};
use App\Exceptions\{AppControlCenterException, ValidationFormRequestException};
use App\Models\Continent;
use App\Repositories\Interfaces\ContinentRepositoryInterface;
use App\Services\BaseApplicationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ContinentService extends BaseApplicationService {

    public function __construct(
        protected ContinentRepositoryInterface $continentRepository
    ) {}

    public function listContinents(?string $search, int $perPage, int $typeList = TypeBrowseEnum::WITHOUT_DELETED): LengthAwarePaginator {
        $this->validationQueryParamsTypeList($typeList);

        return $this->continentRepository->paginateWithSearchAndType($search, $perPage, $typeList);
    }

    public function createContinent(array $payloads): Continent {
        try {
            //* 1. Check validation unique continent
            $this->validationUnique($payloads);

            //* 2. Insert continent
            $result = DB::transaction(function () use ($payloads) {
                return $this->continentRepository->createContinent($payloads);
            });

            return $result;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getContinentById(string $continentId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, array $relations = []): ?Continent {
        $this->validationQueryParamsTypeRead($typeRead);

        try {
            //* 1. Find continent
            $findData = $this->continentRepository->findContinentById($continentId, $typeRead, true, $relations);
            if (!$findData)
                throw new AppControlCenterException(AppAuthResponseCode::NotFound);

            return $findData;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function updateContinent(string $continentId, array $payloads, int $typeUpdate = TypeUpdateEnum::WITHOUT_DELETED): Continent {
        $this->validationQueryParamsTypeUpdate($typeUpdate);

        try {
            //* Find data continent by continent_id
            $findData = $this->continentRepository->findContinentById($continentId, $typeUpdate, true);
            if (!$findData)
                throw new AppControlCenterException(AppAuthResponseCode::NotFound);

            //* Check validation unique continent
            $this->validationUnique($payloads, $continentId);

            //* Update continent
            $result = DB::transaction(function () use ($findData, $payloads) {
                return $this->continentRepository->updateContinent($findData, $payloads);
            });

            return $result;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function deleteContinent(string $continentId, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): array {
        $this->validationQueryParamsTypeDelete($typeDelete);

        //* Determine readType based on typeDelete
        $typeRead = $this->DetermineTypeReadBaseOnTypeDelete($typeDelete);

        try {
            $findData = $this->continentRepository->findContinentById($continentId, $typeRead);
            if (!$findData)
                throw new AppControlCenterException(AppAuthResponseCode::NotFound);

            //* Delete continent
            return DB::transaction(function () use ($findData, $typeDelete) {
                $this->continentRepository->deleteContinent($findData, $typeDelete);

                //* Mapping message from typeDelete
                $message = $this->MappingMessageTypeDelete($typeDelete);

                return compact('message');
            });
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function optionContinent(?string $search, int $perPage = 10, int $typeList = TypeBrowseEnum::WITHOUT_DELETED, array $filterWhereIn = []): LengthAwarePaginator {
        $this->validationQueryParamsTypeList($typeList);

        return $this->continentRepository->paginateWithSearchAndType($search, $perPage, $typeList, $filterWhereIn);
    }

    private function validationUnique(array $payloads, ?string $ignoreId = null): void {
        if ($this->continentRepository->existsByContinentCode($payloads['continent_code'], $ignoreId)) {
            throw new ValidationFormRequestException(context: [
                'continent_code' => __('validation.unique', ['attribute' => __('attributes.continent_code')]),
            ]);
        }
    }
}

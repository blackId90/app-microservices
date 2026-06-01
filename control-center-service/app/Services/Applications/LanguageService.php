<?php

namespace App\Services\Applications;

use App\Enums\{AppAuthResponseCode, TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum, TypeUpdateEnum};
use App\Exceptions\{AppControlCenterException, ValidationFormRequestException};
use App\Models\Language;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Services\BaseApplicationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LanguageService extends BaseApplicationService {

    public function __construct(
        protected LanguageRepositoryInterface $languageRepository
    ) {}

    public function listLanguages(?string $search, int $perPage, int $typeList = TypeBrowseEnum::WITHOUT_DELETED): LengthAwarePaginator {
        $this->validationQueryParamsTypeList($typeList);

        return $this->languageRepository->paginateWithSearchAndType($search, $perPage, $typeList);
    }

    public function createLanguage(array $payloads): Language {
        try {
            //* 1. Check validation unique language
            $this->validationUnique($payloads);

            //* 2. Insert language
            $result = DB::transaction(function () use ($payloads) {
                return $this->languageRepository->createLanguage($payloads);
            });

            return $result;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getLanguageById(string $languageId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, array $relations = []): ?Language {
        $this->validationQueryParamsTypeRead($typeRead);

        try {
            //* 1. Find language
            $findData = $this->languageRepository->findLanguageById($languageId, $typeRead, true, $relations);
            if (!$findData)
                throw new AppControlCenterException(AppAuthResponseCode::NotFound);

            return $findData;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function updateLanguage(string $languageId, array $payloads, int $typeUpdate = TypeUpdateEnum::WITHOUT_DELETED): Language {
        $this->validationQueryParamsTypeUpdate($typeUpdate);

        try {
            //* Find data language by language_id
            $findData = $this->languageRepository->findLanguageById($languageId, $typeUpdate, true);
            if (!$findData)
                throw new AppControlCenterException(AppAuthResponseCode::NotFound);

            //* Check validation unique language
            $this->validationUnique($payloads, $languageId);

            //* Update language
            $result = DB::transaction(function () use ($findData, $payloads) {
                return $this->languageRepository->updateLanguage($findData, $payloads);
            });

            return $result;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function deleteLanguage(string $languageId, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): array {
        $this->validationQueryParamsTypeDelete($typeDelete);

        //* Determine readType based on typeDelete
        $typeRead = $this->DetermineTypeReadBaseOnTypeDelete($typeDelete);

        try {
            $findData = $this->languageRepository->findLanguageById($languageId, $typeRead);
            if (!$findData)
                throw new AppControlCenterException(AppAuthResponseCode::NotFound);

            //* Delete language
            return DB::transaction(function () use ($findData, $typeDelete) {
                $this->languageRepository->deleteLanguage($findData, $typeDelete);

                //* Mapping message from typeDelete
                $message = $this->MappingMessageTypeDelete($typeDelete);

                return compact('message');
            });
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function optionLanguages(?string $search, int $perPage = 10, int $typeList = TypeBrowseEnum::WITHOUT_DELETED, array $filterWhereIn = []): LengthAwarePaginator {
        $this->validationQueryParamsTypeList($typeList);

        return $this->languageRepository->paginateWithSearchAndType($search, $perPage, $typeList, $filterWhereIn);
    }

    private function validationUnique(array $payloads, ?string $ignoreId = null): void {
        if ($this->languageRepository->existsByLanguageCode($payloads['language_code'], $ignoreId)) {
            throw new ValidationFormRequestException(context: [
                'language_code' => __('validation.unique', ['attribute' => __('attributes.language_code')]),
            ]);
        }
    }
}

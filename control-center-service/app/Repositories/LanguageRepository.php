<?php

namespace App\Repositories;

use App\Enums\{TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum};
use App\Models\Language;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LanguageRepository implements LanguageRepositoryInterface {
    private array $searchColumns = ['languages.language_code', 'languages.language_name'];

    public function paginateWithSearchAndType(?string $search, int $perPage = 10, int $typeList = TypeBrowseEnum::WITHOUT_DELETED, array $filterWhereIn = [], string $orderBy = 'languages.created_at', string $orderDirection = 'asc'): LengthAwarePaginator {
        return Language::query()
            ->withFilterTypeList($typeList)
            ->filterWhereIn($filterWhereIn)
            ->filterSearch($search, $this->searchColumns)
            ->orderBy($orderBy, $orderDirection)
            ->paginate($perPage);
    }

    public function createLanguage(array $payloads): Language {
        $language = Language::create([
            'language_code' => $payloads['language_code'],
            'language_name' => $payloads['language_name']
        ]);

        return $language;
    }

    public function findLanguageById(string $languageId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, bool $withTrash = false, array $relations = []): ?Language {
        return Language::query()
            ->withTrashedRelations($relations)
            ->withFilterRead($typeRead, $withTrash)
            ->find($languageId);
    }

    public function updateLanguage(Language $language, array $payloads): Language {
        $language->update($payloads);

        return $language;
    }

    public function deleteLanguage(Language $language, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): bool {
        return $language->performDeleteAction($typeDelete);
    }

    public function existsByLanguageCode(string $languageCode, ?string $ignoreId = null): bool {
        $query = Language::withTrashed()->where('language_code', $languageCode);

        if ($ignoreId)
            $query->where('language_id', '!=', $ignoreId);

        return $query->exists();
    }
}

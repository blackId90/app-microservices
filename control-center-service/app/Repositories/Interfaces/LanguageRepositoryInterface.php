<?php

namespace App\Repositories\Interfaces;

use App\Enums\{TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum};
use App\Models\Language;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface LanguageRepositoryInterface {

    public function paginateWithSearchAndType(?string $search, int $perPage = 10, int $typeList = TypeBrowseEnum::WITHOUT_DELETED, array $filterWhereIn = [], string $orderBy = 'languages.created_at', string $orderDirection = 'asc'): LengthAwarePaginator;

    public function createLanguage(array $payloads): Language;

    public function findLanguageById(string $languageId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, bool $withTrash = false, array $relations = []): ?Language;

    public function updateLanguage(Language $language, array $payloads): Language;

    public function deleteLanguage(Language $language, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): bool;

    public function existsByLanguageCode(string $languageCode, ?string $ignoreId = null): bool;
}

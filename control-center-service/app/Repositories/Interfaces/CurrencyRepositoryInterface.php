<?php

namespace App\Repositories\Interfaces;

use App\Enums\{TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum};
use App\Models\Currency;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CurrencyRepositoryInterface {

    public function paginateWithSearchAndType(?string $search, int $perPage = 10, int $typeList = TypeBrowseEnum::WITHOUT_DELETED, array $filterWhereIn = [], string $orderBy = 'currencies.created_at', string $orderDirection = 'asc'): LengthAwarePaginator;

    public function createCurrency(array $payloads): Currency;

    public function findCurrencyById(string $currencyId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, bool $withTrash = false, array $relations = []): ?Currency;

    public function updateCurrency(Currency $currency, array $payloads): Currency;

    public function deleteCurrency(Currency $currency, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): bool;

    public function existsByCurrencyCode(string $currencyCode, ?string $ignoreId = null): bool;
}

<?php

namespace App\Repositories;

use App\Enums\{TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum};
use App\Models\Currency;
use App\Repositories\Interfaces\CurrencyRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CurrencyRepository implements CurrencyRepositoryInterface {
    private array $searchColumns = ['currencies.currency_code', 'currencies.currency_name'];

    public function paginateWithSearchAndType(?string $search, int $perPage = 10, int $typeList = TypeBrowseEnum::WITHOUT_DELETED, array $filterWhereIn = [], string $orderBy = 'currencies.created_at', string $orderDirection = 'asc'): LengthAwarePaginator {
        return Currency::query()
            ->withFilterTypeList($typeList)
            ->filterWhereIn($filterWhereIn)
            ->filterSearch($search, $this->searchColumns)
            ->orderBy($orderBy, $orderDirection)
            ->paginate($perPage);
    }

    public function createCurrency(array $payloads): Currency {
        $currency = Currency::create([
            'currency_code' => $payloads['currency_code'],
            'currency_name' => $payloads['currency_name'],
            'currency_symbol' => $payloads['currency_symbol'],
            'currency_is_active' => $payloads['currency_is_active']
        ]);

        return $currency;
    }

    public function findCurrencyById(string $currencyId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, bool $withTrash = false, array $relations = []): ?Currency {
        return Currency::query()
            ->withTrashedRelations($relations)
            ->withFilterRead($typeRead, $withTrash)
            ->find($currencyId);
    }

    public function updateCurrency(Currency $currency, array $payloads): Currency {
        $currency->update($payloads);

        return $currency;
    }

    public function deleteCurrency(Currency $currency, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): bool {
        return $currency->performDeleteAction($typeDelete);
    }

    public function existsByCurrencyCode(string $currencyCode, ?string $ignoreId = null): bool {
        $query = Currency::withTrashed()->where('currency_code', $currencyCode);

        if ($ignoreId)
            $query->where('currency_id', '!=', $ignoreId);

        return $query->exists();
    }
}

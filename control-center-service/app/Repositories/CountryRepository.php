<?php

namespace App\Repositories;

use App\Enums\{TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum};
use App\Models\Country;
use App\Repositories\Interfaces\CountryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CountryRepository implements CountryRepositoryInterface {
    private array $searchColumns = ['countries.country_code', 'countries.country_alpha_3', 'countries.country_name', 'countries.country_capital', 'countries.country_phone'];

    public function paginateWithSearchAndType(?string $search, int $perPage = 10, int $typeList = TypeBrowseEnum::WITHOUT_DELETED, array $filterWhereIn = [], string $orderBy = 'countries.created_at', string $orderDirection = 'asc'): LengthAwarePaginator {
        return Country::query()
            ->leftJoin('continents', function ($join) {
                $join->on('continents.continent_code', '=', 'countries.country_continent_code');
                // ->whereNull('auth_permissions_parent.deleted_at'); // Filter Trash untuk Parent
            })
            ->leftJoin('currencies', function ($join) {
                $join->on('currencies.currency_code', '=', 'countries.country_currency_code');
                // ->whereNull('auth_permissions_parent.deleted_at'); // Filter Trash untuk Parent
            })
            ->select([
                'countries.*',

                //* continents
                'continents.continent_id as continents_continent_id',
                'continents.continent_code as continents_continent_code',
                'continents.continent_name as continents_continent_name',
                'continents.created_at as continents_created_at',
                'continents.updated_at as continents_updated_at',
                'continents.deleted_at as continents_deleted_at',

                //* currencies
                'currencies.currency_id as currencies_currency_id',
                'currencies.currency_code as currencies_currency_code',
                'currencies.currency_name as currencies_currency_name',
                'currencies.currency_symbol as currencies_currency_symbol',
                'currencies.currency_is_active as currencies_currency_is_active',
                'currencies.created_at as currencies_created_at',
                'currencies.updated_at as currencies_updated_at',
                'currencies.deleted_at as currencies_deleted_at'
            ])
            ->withFilterTypeList($typeList)
            ->filterWhereIn($filterWhereIn)
            ->filterSearch($search, $this->searchColumns)
            ->orderBy($orderBy, $orderDirection)
            ->paginate($perPage);
    }

    public function createCountry(array $payloads): Country {
        $country = Country::create([
            'country_code' => $payloads['country_code'],
            'country_alpha_3' => $payloads['country_alpha_3'],
            'country_name' => $payloads['country_name'],
            'country_capital' => $payloads['country_capital'],
            'country_phone' => $payloads['country_phone'],
            'country_continent_code' => $payloads['country_continent_code'],
            'country_currency_code' => $payloads['country_currency_code']
        ]);

        return $country;
    }

    public function findCountryById(string $countryId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, bool $withTrash = false, array $relations = []): ?Country {
        return Country::query()
            ->withTrashedRelations($relations)
            ->withFilterRead($typeRead, $withTrash)
            ->find($countryId);
    }

    public function updateCountry(Country $country, array $payloads): Country {
        $country->update($payloads);

        return $country;
    }

    public function deleteCountry(Country $country, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): bool {
        return $country->performDeleteAction($typeDelete);
    }

    public function existsByCountryCode(string $countryCode, ?string $ignoreId = null): bool {
        $query = Country::withTrashed()->where('country_code', $countryCode);

        if ($ignoreId)
            $query->where('country_id', '!=', $ignoreId);

        return $query->exists();
    }

    public function existsByCountryAlpha3(string $countryAlpha3, ?string $ignoreId = null): bool {
        $query = Country::withTrashed()->where('country_alpha_3', $countryAlpha3);

        if ($ignoreId)
            $query->where('country_id', '!=', $ignoreId);

        return $query->exists();
    }
}

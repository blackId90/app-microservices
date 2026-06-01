<?php

namespace App\Repositories\Interfaces;

use App\Enums\{TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum};
use App\Models\Country;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CountryRepositoryInterface {

    public function paginateWithSearchAndType(?string $search, int $perPage = 10, int $typeList = TypeBrowseEnum::WITHOUT_DELETED, array $filterWhereIn = [], string $orderBy = 'countries.created_at', string $orderDirection = 'asc'): LengthAwarePaginator;

    public function createCountry(array $payloads): Country;

    public function findCountryById(string $countryId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, bool $withTrash = false, array $relations = []): ?Country;

    public function updateCountry(Country $country, array $payloads): Country;

    public function deleteCountry(Country $country, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): bool;

    public function existsByCountryCode(string $countryCode, ?string $ignoreId = null): bool;

    public function existsByCountryAlpha3(string $countryAlpha3, ?string $ignoreId = null): bool;
}

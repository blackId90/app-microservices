<?php

namespace App\Repositories\Interfaces;

use App\Enums\{TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum};
use App\Models\Continent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ContinentRepositoryInterface {

    public function paginateWithSearchAndType(?string $search, int $perPage = 10, int $typeList = TypeBrowseEnum::WITHOUT_DELETED, array $filterWhereIn = [], string $orderBy = 'continents.created_at', string $orderDirection = 'asc'): LengthAwarePaginator;

    public function createContinent(array $payloads): Continent;

    public function findContinentById(string $continentId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, bool $withTrash = false, array $relations = []): ?Continent;

    public function updateContinent(Continent $continent, array $payloads): Continent;

    public function deleteContinent(Continent $continent, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): bool;

    public function existsByContinentCode(string $continentCode, ?string $ignoreId = null): bool;
}

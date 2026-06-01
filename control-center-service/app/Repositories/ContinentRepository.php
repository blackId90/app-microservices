<?php

namespace App\Repositories;

use App\Enums\{TypeBrowseEnum, TypeDeleteEnum, TypeReadEnum};
use App\Models\Continent;
use App\Repositories\Interfaces\ContinentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContinentRepository implements ContinentRepositoryInterface {
    private array $searchColumns = ['continents.continent_code', 'continents.continent_name'];

    public function paginateWithSearchAndType(?string $search, int $perPage = 10, int $typeList = TypeBrowseEnum::WITHOUT_DELETED, array $filterWhereIn = [], string $orderBy = 'continents.created_at', string $orderDirection = 'asc'): LengthAwarePaginator {
        return Continent::query()
            ->withFilterTypeList($typeList)
            ->filterWhereIn($filterWhereIn)
            ->filterSearch($search, $this->searchColumns)
            ->orderBy($orderBy, $orderDirection)
            ->paginate($perPage);
    }

    public function createContinent(array $payloads): Continent {
        $continent = Continent::create([
            'continent_code' => $payloads['continent_code'],
            'continent_name' => $payloads['continent_name']
        ]);

        return $continent;
    }

    public function findContinentById(string $continentId, int $typeRead = TypeReadEnum::WITHOUT_DELETED, bool $withTrash = false, array $relations = []): ?Continent {
        return Continent::query()
            ->withTrashedRelations($relations)
            ->withFilterRead($typeRead, $withTrash)
            ->find($continentId);
    }

    public function updateContinent(Continent $continent, array $payloads): Continent {
        $continent->update($payloads);

        return $continent;
    }

    public function deleteContinent(Continent $continent, int $typeDelete = TypeDeleteEnum::SOFT_DELETE): bool {
        return $continent->performDeleteAction($typeDelete);
    }

    public function existsByContinentCode(string $continentCode, ?string $ignoreId = null): bool {
        $query = Continent::withTrashed()->where('continent_code', $continentCode);

        if ($ignoreId)
            $query->where('continent_id', '!=', $ignoreId);

        return $query->exists();
    }
}
